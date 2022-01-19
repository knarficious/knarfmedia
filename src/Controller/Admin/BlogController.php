<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Post;
use App\Entity\Media;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Security\PostVoter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Comment;
use App\Form\CommentType;

/**
* @Route("/admin")
* @IsGranted("ROLE_ADMIN")
*/ 
class BlogController extends AbstractController
{
    /**
     * 
     * Liste tous les posts
     * 
     * @Route("/posts", methods="GET", name="admin_index")
     * @Route("/posts", methods="GET", name="admin_post_index")
     */
    public function index(PostRepository $posts): Response
    {
        $authorPosts = $posts->findAll();
        
        return $this->render('admin/blog/index.html.twig', ['posts' => $authorPosts]);
    }
    
    /**
     * @Route("/posts/new", methods="GET|POST", name="admin_post_new")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
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
            
            $em = $this->getDoctrine()->getManager();
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
                return $this->redirectToRoute('admin_post_new');
            }
            
            return $this->redirectToRoute('admin_post_index');
        }
        
        return $this->render('admin/blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * Finds and displays a Post entity.
     *
     * @Route("/posts/{id<\d+>}", methods="GET", name="admin_post_show")
     */
    public function show(Post $post): Response
    {
        // This security check can also be performed
        // using an annotation: @IsGranted("show", subject="post", message="Posts can only be shown to their authors.")
        // $this->denyAccessUnlessGranted(PostVoter::SHOW, $post, 'Posts can only be shown to their authors.');
        
        return $this->render('admin/blog/show.html.twig', [
            'post' => $post,
        ]);
    }
    
    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/posts/{id<\d+>}/edit", methods="GET|POST", name="admin_post_edit")
     * @IsGranted("edit", subject="post", message="Posts can only be edited by their authors.")
     */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            
            $this->addFlash('success', 'post.updated_successfully');
            
            return $this->redirectToRoute('admin_post_edit', ['id' => $post->getId()]);
        }
        
        return $this->render('admin/blog/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * Deletes a Post entity.
     *
     * @Route("/posts/{id}/delete", methods="POST", name="admin_post_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Post $post): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_post_index');
        }
        
        // Delete the tags associated with this blog post. This is done automatically
        // by Doctrine, except for SQLite (the database used in this application)
        // because foreign key support is not enabled by default in SQLite
        $post->getTags()->clear();
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        
        $this->addFlash('success', 'post.deleted_successfully');
        
        return $this->redirectToRoute('admin_post_index');
    }
    

    
    /**
     * Deletes a Comment entity.
     *
     * @Route("/comments/{id}/delete", methods="GET|POST", name="admin_comment_delete")
     */
    public function deleteComment(Request $request, Comment $comment): Response
    {
//         if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
//             return $this->redirectToRoute('admin_post_index');
//         }

        
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();
        
        $this->addFlash('success', 'commentaire supprimé');
        
        return $this->redirectToRoute('admin_post_index');
    }
}
