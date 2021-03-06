<?php

// src/Knarf/PlatformBundle/Controller/AdvertController.php

namespace Knarf\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Knarf\PlatformBundle\Form\AdvertType;
use Knarf\PlatformBundle\Form\AdvertEditType;
use Knarf\PlatformBundle\Form\AdminAdvertType;
use Knarf\PlatformBundle\Entity\Advert;
use Knarf\PlatformBundle\Entity\Media;

class AdvertController extends Controller {

    public function indexAsAdminAction(Request $request)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('KnarfPlatformBundle:Advert');
        $listAdverts = $repository->getAdminAdverts();
        
        return $this->render('KnarfPlatformBundle:Advert:index_as_admin.html.twig',
                ['listAdverts' => $listAdverts ]);
    }
    
    /**
     * @Route("/articles")
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $advertsRep = $em->getRepository('KnarfPlatformBundle:Advert');
        $allAdvertsQuery = $advertsRep->createQueryBuilder('a')
                ->andWhere('a.published = 1')
                ->andWhere('a.isAdmin = 0')
                ->getQuery();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $allAdvertsQuery,
                $request->query->getInt('page', 1),
                $request->query->getInt('limit', 6),
                [
                'defaultSortFieldName'      => 'a.date',
                'defaultSortDirection' => 'desc'
                ]                
                );

        return $this->render('KnarfPlatformBundle:Advert:index.html.twig', array(
                    'pagination' => $pagination
        ));
    }

//    /**
//     * @Route("/annonces/{id}", name="knarf_platform_view")
//     * @ParamConverter("advert", class="KnarfPlatformBundle:Advert")
//     * @param Advert $advert
//     * @param Request $request
//     * @return Response
//     */
//    public function showAction(Advert $advert, Request $request)
//    {
//        $response = new Response();
//        
//        if ($response->isNotModified($request)) {
//            // envoie la r??ponse 304 tout de suite
//            return $response;
//        }
//        return $this->render('KnarfPlatformBundle:Advert:view.html.twig', ['advert' => $advert], $response);
//    }

    /**
     * @Route("/article/{slug}", name="knarf_platform_view")
     * @param type $slug
     * @param Request $request
     */
    public function viewAction($slug, Request $request) {

        $repository1 = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('KnarfPlatformBundle:Advert');

        $advert = $repository1->findOneBy(array('slug' => $slug));

        if (null === $advert) {
            throw new NotFoundHttpException("L'article " . $slug . " n'existe pas!");
        }
        
        $seoPage = $this->container->get('sonata.seo.page');
        
        $seoPage->setTitle($advert->getTitle())
                ->addMeta('name', 'description', $advert->getContent())
                ->addMeta('property', 'og:locale', 'fr_FR')
                ->addMeta('property', 'og:title', $advert->getTitle())
                ->addMeta('property', 'og:type', 'article')
                ->addMeta('property', 'og:url',  $this->generateUrl('knarf_platform_view', ['slug'=> $advert->getSlug()
                ], UrlGeneratorInterface::ABSOLUTE_URL))
                ->addMeta('property', 'og:description', $advert->getContent())
                ->addMeta('property', 'og:image', $this->getParameter('base_url').$this->get('vich_uploader.templating.helper.uploader_helper')->asset($advert->getMedia(), 'mediaFile'))
                ->addMeta('property', 'article:published_time', date_format($advert->getDate(), 'd/m/Y'))
                ->addMeta('property', 'article:modified_time', date_format($advert->getUpdateAt(), 'd/m/y'))
                ->addMeta('property', 'twitter:card', 'summary')
                ->addMeta('property', 'twitter:title', $advert->getTitle())
                ->addMeta('property', 'twitter:description', $advert->getContent())
                ->addMeta('property', 'twitter:image', $this->getParameter('base_url').$this->get('vich_uploader.templating.helper.uploader_helper')->asset($advert->getMedia(), 'mediaFile'))
                ->addMeta('property', 'twitter:url', $this->generateUrl('knarf_platform_view', ['slug'=> $advert->getSlug()
                ], UrlGeneratorInterface::ABSOLUTE_URL))
;

        return $this->render('KnarfPlatformBundle:Advert:view.html.twig', array(
                    'advert' => $advert, 'active' => 'advert'));
    }
    
        /**
     * @Route("/legal/{slug}", name="knarf_admin_view")
     * @param type $slug
     * @param Request $request
     */
    public function viewAsAdminAction($slug, Request $request) {

        $repository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('KnarfPlatformBundle:Advert');

        $advert = $repository->findOneBy(array('slug' => $slug));

        if (null === $advert) {
            throw new NotFoundHttpException("L'article " . $slug . " n'existe pas!");
        }

        return $this->render('KnarfPlatformBundle:Advert:vue.html.twig', array(
                    'advert' => $advert));
    }

    /**
     * @Route("/ajout", name="knarf_platform_add")
     * @param Request $request
     * @return type
     */
    public function addAction(Request $request) {
        $advert = new Advert();
        $media = new Media();
        $user = $this->getUser();
        $advert->setUser($user);
        $advert->setDate(new \DateTime());
        $advert->setUpDateAt(new \DateTime());
        $media->setDate(new \DateTime());
        $advert->setMedia($media);
        $form = $this->createForm(AdvertType::class, $advert);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $media->setNomMedia($form->getData()->getMedia()->getNomMedia(), $form->getData()->getMedia()->getMediaFile());
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->persist($media);
            $em->flush();

            $this->addFlash('success', 'Annonce bien enregistr??e.');

            // Puis on redirige vers la page de visualisation de cettte annonce

            return $this->redirect($this->generateUrl('knarf_platform_view', array('id' => $advert->getId(), 'slug' => $advert->getSlug())));
        }

        // Si on n'est pas en POST, alors on affiche le formulaire

        return $this->render('KnarfPlatformBundle:Advert:ajout.html.twig', array('form' => $form->createView()));
    }
    
    /**
     * @Route("/admin/ajout", name="as_admin_add")
     * @param Request $request
     * @return type
     */
    public function addAsAdminAction(Request $request) 
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $advert = new Advert();
        $user = $this->getUser();
        $advert->setUser($user);
        $advert->setDate(new \DateTime());
        $advert->setUpDateAt(new \DateTime());
        $form = $this->createForm(AdminAdvertType::class, $advert);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            $this->addFlash('success', 'Annonce bien enregistr??e.');

            // Puis on redirige vers la page de visualisation de cettte annonce

            return $this->redirect($this->generateUrl('knarf_admin_view', array('id' => $advert->getId(), 'slug' => $advert->getSlug())));
        }

        // Si on n'est pas en POST, alors on affiche le formulaire

        return $this->render('KnarfPlatformBundle:Advert:admin_ajout.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/modifier/{slug}", name="knarf_platform_edit")
     * @param type $slug
     * @param Request $request
     * @throws NotFoundHttpException
     */
    public function editAction($slug, Request $request) {

        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('KnarfPlatformBundle:Advert')->findOneBy(array('slug' => $slug));

        $advert->setUpDateAt(new \DateTime());

        //On v??rifie si l'annonce appartient ?? l'utilisateur en cours
        if ($this->getUser() === $advert->getUser()) {
            if (null === $advert) {
                throw new NotFoundHttpException("L'annonce  " . $slug . " n'existe pas!");
            }

            $form = $this->createForm(AdvertEditType::class, $advert);

            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $em->persist($advert);
                $em->flush();

                $this->addFlash('success', 'Annonce modifi??e avec succ??s.');

                return $this->redirectToRoute('knarf_platform_view', array('slug' => $advert->getSlug()));
            }


            return $this->render('KnarfPlatformBundle:Advert:edit.html.twig',
                            array('advert' => $advert, 'form' => $form->createView()));
        } else {
            return $this->redirectToRoute('knarf_platform_view', array('slug' => $advert->getSlug()));
        }
    }
    
       /**
     * @Route("/admin/modifier/{slug}", name="knarf_admin_edit")
     * @param type $slug
     * @param Request $request
     * @throws NotFoundHttpException
     */
    public function editAsAdminAction($slug, Request $request) 
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('KnarfPlatformBundle:Advert')->findOneBy(array('slug' => $slug));

        $advert->setUpDateAt(new \DateTime());

        //On v??rifie si l'annonce appartient ?? l'utilisateur en cours
        if ($this->getUser() === $advert->getUser()) {
            if (null === $advert) {
                throw new NotFoundHttpException("L'annonce  " . $slug . " n'existe pas!");
            }

            $form = $this->createForm(AdminAdvertType::class, $advert);

            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $em->persist($advert);
                $em->flush();

                $this->addFlash('success', 'Annonce modifi??e avec succ??s.');

                return $this->redirectToRoute('knarf_admin_view', array('slug' => $advert->getSlug()));
            }


            return $this->render('KnarfPlatformBundle:Advert:admin_ajout.html.twig',
                            array('advert' => $advert, 'form' => $form->createView()));
        } else {
            return $this->redirectToRoute('knarf_platform_view', array('slug' => $advert->getSlug()));
        }
    }

    /**
     * @Route("/delete/{slug}", name="knarf_platform_delete")
     * @param type $slug
     * @param Request $request
     * @throws NotFoundHttpException
     */
    public function deleteAdvertAction($slug, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('KnarfPlatformBundle:Advert')->findOneBy(array('slug' => $slug));

        if ($this->getUser() === $advert->getUser()) {
            if (null === $advert) {
                throw new NotFoundHttpException("L'annonce  " . $slug . " n'existe pas!");
            }

            $form = $this->createFormBuilder()->getForm();

            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $em->remove($advert);
                $em->flush();

                $this->addFlash('success', "L'annonce a ??t?? supprim??e avec succ??s");

                return $this->redirect($this->generateUrl('profile'));
            }

            return $this->render('KnarfPlatformBundle:Advert:delete.html.twig', array(
                        'advert' => $advert,
                        'form' => $form->createView()
            ));
        } else {
            return $this->redirectToRoute('knarf_platform_view', array('slug' => $advert->getSlug()));
        }
    }
    
    /**
     * @Route("admin/delete/{slug}", name="knarf_admin_delete")
     * @param type $slug
     * @param Request $request
     * @throws NotFoundHttpException
     */
    public function deleteAdvertAsAdminAction($slug, Request $request) 
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('KnarfPlatformBundle:Advert')->findOneBy(array('slug' => $slug));

        if ($this->getUser() === $advert->getUser()) {
            if (null === $advert) {
                throw new NotFoundHttpException("L'annonce  " . $slug . " n'existe pas!");
            }

            $form = $this->createFormBuilder()->getForm();

            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $em->remove($advert);
                $em->flush();

                $this->addFlash('success', "L'article a ??t?? supprim?? avec succ??s");

                return $this->redirect($this->generateUrl('profile'));
            }

            return $this->render('KnarfPlatformBundle:Advert:delete_as_admin.html.twig', array(
                        'advert' => $advert,
                        'form' => $form->createView()
            ));
        } else {
            return $this->redirectToRoute('knarf_platform_view', array('slug' => $advert->getSlug()));
        }
    }

    public function menuAction() {
        $listAdverts = $this->getDoctrine()->getManager()->getRepository('KnarfPlatformBundle:Advert')->getLastAdverts();

        return $this->render('KnarfPlatformBundle:Advert:menu.html.twig', array(
                    'listAdverts' => $listAdverts
        ));
    }
    
    public function derniersArticlesAction()
    {
        $listAdverts = $this->getDoctrine()->getManager()->getRepository('KnarfPlatformBundle:Advert')->getLastAdverts();

        return $this->render('KnarfPlatformBundle:Advert:derniers_articles.html.twig', array(
                    'listAdverts' => $listAdverts
        ));
    }

    public function getAdvertManager() {
        return $this->get('app.advert.manager');
    }

    public function getAdvertRepository() {
        return $this->get('app.advert.repository');
    }

}
