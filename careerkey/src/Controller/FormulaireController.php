<?php

namespace App\Controller;
use App\Form\HoussemType;
use App\Form\TestType;
use App\Controller\HoussemController;
use App\Repository\QuestionRepository;
use App\Entity\Formulaire;
use App\Form\FormulaireType;
use App\Repository\FormulaireRepository;
use App\Repository\ReponsesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Knp\Component\Pager\PaginatorInterface;

class FormulaireController extends Controller
{
    /**
     * @Route("/base", name="accueil_index", methods={"GET"})
     */
    public function index(FormulaireRepository $formulaireRepository): Response
    {
        return $this->render('index/index.html.twig', [
            'formulaires' => $formulaireRepository->findAll(),
        ]);
    }
    /**
     * @Route("/houssem", name="houssem")
     */
    public function index33(Request $request): Response
    {
        $form=$this->createForm(HoussemType::class);
        $form->handleRequest($request);
        return $this->render('houssem/index.html.twig', [
            'f' => $form->createView(),
        ]);
    }
    /**
     * @Route("/dashboard", name="dashboard_index", methods={"GET"})
     */
    public function dashboard(FormulaireRepository $formulaireRepository): Response
    {
        return $this->render('index/dashboard.html.twig', [
            'formulaires' => $formulaireRepository->findAll(),
        ]);
    }
    /**
     * @Route("/list", name="formulaire_list", methods={"GET"})
     */
    public function list(FormulaireRepository $formulaireRepository): Response
    {
        return $this->render('formulaire/index.html.twig', [
            'formulaire' => $formulaireRepository->findAll(),
        ]);
    }
    /**
     * @Route("/view_forms_front", name="formulaire_front", methods={"GET"})
     */
    public function passer_entretiens(FormulaireRepository $formulaireRepository , Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $appointmentsRepository = $em->getRepository(Formulaire::class);

        $liste = $appointmentsRepository->findAll();

        $appointments = $this->get('knp_paginator')->paginate(
        // Doctrine Query, not results
            $liste,
            // Define the page parameter
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 3)
            // Items per page
        );
        return $this->render('forms_view.html.twig', [
            'formulaire' => $appointments,
        ]);
    }


    /**
     * @Route("/new", name="formulaire_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $formulaire = new Formulaire();
        $form = $this->createForm(FormulaireType::class, $formulaire);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($formulaire);
            $entityManager->flush();

            return $this->redirectToRoute('formulaire_list');
        }


        return $this->render('formulaire/new.html.twig', [
            'formulaire' => $formulaire,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="formulaire_show", methods={"GET"})
     */
    public function show(Formulaire $formulaire): Response
    {
        return $this->render('formulaire/show.html.twig', [
            'formulaire' => $formulaire,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="formulaire_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Formulaire $formulaire): Response
    {
        $form = $this->createForm(FormulaireType::class, $formulaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('formulaire_list');
        }

        return $this->render('formulaire/edit.html.twig', [
            'formulaire' => $formulaire,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="formulaire_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Formulaire $formulaire): Response
    {
        if ($this->isCsrfTokenValid('delete'.$formulaire->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($formulaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('formulaire_list');
    }
    /**
     * @Route ("formulaire/searchDescription" , name="searchNBQUESTION")
     */
    function searchNBquestion(FormulaireRepository $repository,Request $request)
    {
        $data = $request->get('search');
        $formulaire = $repository->search($data);
        return $this->render('formulaire/index.html.twig', [
            'formulaire' => $formulaire,
        ]);
    }
        /**
         * @Route ("formulaire/filterByID" , name="formByIdASC")
         */
        function filterById(FormulaireRepository $repository, Request $request)
        {
            $formulaire = $repository->FilterByID();
            return $this->render('formulaire/index.html.twig', [
                'formulaire' => $formulaire,
            ]);
        }

        /**
         * @Route ("formulaire/filterByDate" , name="formByDate")
         */
        function filterByDate(FormulaireRepository $repository, Request $request)
        {
            $formulaire = $repository->FilterByDate();
            return $this->render('formulaire/index.html.twig', [
                'formulaire' => $formulaire,
            ]);
        }
    /**
     * @Route ("/{id}/listQuestionsByForms" , name="listQuestionsByForms")
     */
    function listQuestionsByForm(Request $request,QuestionRepository $questionRepository,FormulaireRepository $formulaireRepository,$id)
    {
        //recuperer Questions selon formulaire
        $forms = $formulaireRepository->find($id);
        /*
        $viewParams = ['quiz' => $forms];

        return $this->render(
            'quest_rep_list.html.twig',
            $viewParams
        );

        */
        //dd($forms); afficher le contenu de formulaire
        $question = $questionRepository->listQuestionsByForm($forms->getId());
        //dd($question);


        //dd($from);
        //recuperer reponses selon question
        //dd($question);
        $next = 0;


        foreach ($question as $q) {

            //tableau qui va stocker les reponses de chaque question
            $techChoices = array();
            // liste des réponses de la question concerné


            $listerep = $q->getReponses();

            foreach ($listerep as $r) {
                $techChoices[$r->getDescription()] = $r->getDescription();
            }
            $from = $this->createFormBuilder()
                ->add('Reponse', ChoiceType::class, [
                    'label' => $q->getDescription(),
                    'choices' => $techChoices,
                    'expanded' => true,
                    'multiple' => false,

                ])
                ->getForm();

            $from->add('valider' ,SubmitType::class);
            $from->handleRequest($request);

            if($from->isSubmitted() && $from->isValid()){
                $data = $from->getData();

                //dd($data);


            }
            return $this->render('houssem/index.html.twig', [
                'f' => $from->createView(),

            ]);


        }


    }

    /**
     * @Route ("formulaire/stats_page" , name="stats_page")
     */
    function stats_page(FormulaireRepository $repository,QuestionRepository $questionRepository , Request $request)
    {
        $total_forms = $repository->Total_formulaire();
        $total_question = $questionRepository->Total_questions();
        return $this->render('stats_entretiens.html.twig', [
            'total_f' => $total_forms,
            'total_q' => $total_question
        ]);
    }





}
