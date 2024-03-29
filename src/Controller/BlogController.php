<?php


namespace App\Controller;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Media;
use App\Entity\Tag;
use App\Event\CommentCreatedEvent;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * 
 * @author franck
 * 
 * @Route("/blog")
 *
 */
class BlogController extends AbstractController 
{
    public function __construct(private ManagerRegistry $manager) {
        
    }

    /**
     * 
     * @Route("/", defaults={"page": "1", "_format"="html"}, methods="GET", name="blog_index")
     * @Route("/rss.xml", defaults={"page": "1", "_format"="xml"}, methods="GET", name="blog_rss")
     * @Route("/page/{page<[1-9]\d*>}", defaults={"_format"="html"}, methods="GET", name="blog_index_paginated")
     * @Cache(smaxage="10")
     */
    public function index(Request $request, int $page, string $_format, PostRepository $posts, TagRepository $tags): Response
    {
        $tag = null;
        if ($request->query->has('tag')) {
            $tag = $tags->findOneBy(['name' => $request->query->get('tag')]);
        }
        $latestPosts = $posts->findLatest($page, $tag);
        
        // Every template name also has two extensions that specify the format and
        // engine for that template.
        // See https://symfony.com/doc/current/templates.html#template-naming
        return $this->render('blog/index.'.$_format.'.twig', [
            'paginator' => $latestPosts,
            'tagName' => $tag ? $tag->getName() : null,
        ]);
    }
    
    public function suggest(Request $request, PostRepository $posts, Tag $tag): Response
    {
        $posts->findBy($tag);
        
        return $this->render('blog/_posts_suggestions.html.twig', ['posts' => $posts]);
    }
    
    /**
     * @Route("/posts/{slug}", methods="GET", name="blog_post")
     *
     * NOTE: The $post controller argument is automatically injected by Symfony
     * after performing a database query looking for a Post with the 'slug'
     * value given in the route.
     * See https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
     */
    public function postShow(Post $post): Response
    {
        // Symfony's 'dump()' function is an improved version of PHP's 'var_dump()' but
        // it's not available in the 'prod' environment to prevent leaking sensitive information.
        // It can be used both in PHP files and Twig templates, but it requires to
        // have enabled the DebugBundle. Uncomment the following line to see it in action:
        //
        // dump($post, $this->getUser(), new \DateTime());
        
        return $this->render('blog/post_show.html.twig', ['post' => $post]);
    }
    
    /**
     * @Route("/new", methods="GET|POST", name="blog_post_new")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $post = new Post();
        $media1 = new Media();
        $post->setAuthor($this->getUser());
        $post->getMedias()->add($media1);
        
        $form = $this->createForm(PostType::class, $post)
        ->add('saveAndCreateNew', SubmitType::class);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $data[] = $form->getData()->getMedias();
            foreach ( $data as $media) {
                foreach ($media as $entity){
                    $nomMedia = $entity->getName();
                    $media1->setName($nomMedia);
                }
            }
            
            $em = $this->manager->getManager();
            $em->persist($post);
            $em->persist($media1);
            //$em->persist($media2);
            $em->flush();
            
            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/controller.html#flash-messages
            $this->addFlash('success', 'post.created_successfully');
            
            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('blog_post_new');
            }
            
            return $this->redirectToRoute('blog_post', [ 'slug' => $post->getSlug()] );
        }
        
        return $this->render('blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id<\d+>}/edit", methods="GET|POST", name="blog_post_edit")
     * @IsGranted("edit", subject="post", message="Posts can only be edited by their authors.")
     */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $post->setUpdatedAt(new \DateTimeImmutable());
            $this->manager->getManager()->flush();
            
            $this->addFlash('success', 'post.updated_successfully');
            
            return $this->redirectToRoute('blog_post', ['slug' => $post->getSlug()]);
        }
        
        return $this->render('blog/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}/delete", methods="POST", name="blog_post_delete")
     * @IsGranted("delete", subject="post")
     */
    public function delete(Request $request, Post $post): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('blog_index');
        }
        
        // Delete the tags associated with this blog post. This is done automatically
        // by Doctrine, except for SQLite (the database used in this application)
        // because foreign key support is not enabled by default in SQLite
        $post->getTags()->clear();
        
        $em = $this->manager->getManager();
        $em->remove($post);
        $em->flush();
        
        $this->addFlash('success', 'post.deleted_successfully');
        
        return $this->redirectToRoute('blog_index');
    }
    
    /**
     * @Route("/comment/{postSlug}/new", methods="POST", name="comment_new")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @ParamConverter("post", options={"mapping": {"postSlug": "slug"}})
     *
     * NOTE: The ParamConverter mapping is required because the route parameter
     * (postSlug) doesn't match any of the Doctrine entity properties (slug).
     * See https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html#doctrine-converter
     */
    public function commentNew(Request $request, Post $post, EventDispatcherInterface $eventDispatcher): Response
    {
        $comment = new Comment();
        $comment->setAuthor($this->getUser());
        $comment->setPublishedAt(new \DateTimeImmutable());
        $post->addComment($comment);
        
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->manager->getManager();
            $em->persist($comment);
            $em->flush();
            
            // When an event is dispatched, Symfony notifies it to all the listeners
            // and subscribers registered to it. Listeners can modify the information
            // passed in the event and they can even modify the execution flow, so
            // there's no guarantee that the rest of this controller will be executed.
            // See https://symfony.com/doc/current/components/event_dispatcher.html
            $eventDispatcher->dispatch(new CommentCreatedEvent($comment));
            $this->addFlash('success', 'Commentaire publié');
            return $this->redirect($this->generateUrl('blog_post', ['slug' => $post->getSlug()])."#comments");
        }
        
        return $this->render('blog/comment_form_error.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * This controller is called directly via the render() function in the
     * blog/post_show.html.twig template. That's why it's not needed to define
     * a route name for it.
     *
     * The "id" of the Post is passed in and then turned into a Post object
     * automatically by the ParamConverter.
     */
    public function commentForm(Post $post): Response
    {
        $form = $this->createForm(CommentType::class);
        
        return $this->render('blog/_comment_form.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/search", methods="GET", name="blog_search")
     */
    public function search(Request $request, PostRepository $posts): Response
    {
        $query = $request->query->get('q', '');
        $limit = $request->query->get('l', 10);
        
        if (!$request->isXmlHttpRequest()) {
            return $this->render('blog/search.html.twig', ['query' => $query]);
        }
        
        $foundPosts = $posts->findBySearchQuery($query, $limit);
        
        $results = [];
        foreach ($foundPosts as $post) {
            $results[] = [
                'title' => htmlspecialchars($post->getTitle(), ENT_COMPAT | ENT_HTML5),
                'date' => $post->getPublishedAt()->format('M d, Y'),
                'author' => htmlspecialchars($post->getAuthor()->getFullName(), ENT_COMPAT | ENT_HTML5),
                'summary' => htmlspecialchars($post->getSummary(), ENT_COMPAT | ENT_HTML5),
                'url' => $this->generateUrl('blog_post', ['slug' => $post->getSlug()]),
            ];
        }
        
        return $this->json($results);
    }
}
