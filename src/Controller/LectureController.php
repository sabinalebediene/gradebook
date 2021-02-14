<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Student;
use App\Entity\Lecture;
use App\Entity\Grade;

class LectureController extends AbstractController
{
    /**
     * @Route("/lecture", name="lecture_index")
     */
    public function index(Request $r): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $students = $this->getDoctrine()
        ->getRepository(Student::class)
        ->findAll();


        $lectures = $this->getDoctrine()
        ->getRepository(Lecture::class);

        if ('title_az' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $lectures = $lectures->findBy([],['title'=>'asc']);
        }

        elseif ('title_za' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $lectures = $lectures->findBy([],['title'=>'desc']);
        }
        else {
            $lectures = $lectures->findAll();
        }


        return $this->render('lecture/index.html.twig', [
            'lectures' => $lectures,
            'students' => $students,
            'sortBy' => $r->query->get('sort') ?? 'default',
            'success' => $r->getSession()->getFlashBag()->get('success', []),
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    /**
     * @Route("/lecture/create", name="lecture_create", methods={"GET"})
     */
    public function create(Request $r): Response
    {

        $lecture_title = $r->getSession()->getFlashBag()->get('lecture_title', []);
        $lecture_description = $r->getSession()->getFlashBag()->get('lecture_description', []);

        return $this->render('lecture/create.html.twig', [
            'lecture_title' => $lecture_title[0] ?? '',
            'lecture_description' => $lecture_description[0] ?? '',
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    /**
     * @Route("/lecture/store", name="lecture_store", methods={"POST"})
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {


        $lecture = new Lecture;
        $lecture
        ->setTitle($r->request->get('lecture_title'))
        ->setDescription($r->request->get('lecture_description'));

        // tikriname pagal assertus 
        // validacija
        $errors = $validator->validate($lecture);

        // jei yra error, verciame i string ir ji graziname, parodo error'a
        if (count($errors) > 0) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            $r->getSession()->getFlashBag()->add('lecture_title', $r->request->get('lecture_title'));
            $r->getSession()->getFlashBag()->add('lecture_description', $r->request->get('lecture_description'));
            return $this->redirectToRoute('lecture_create');
            
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($lecture);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', $lecture->getTitle().' sekmingai sukurtas.');

        return $this->redirectToRoute('lecture_index');
    }

    /**
     * @Route("/lecture/edit/{id}", name="lecture_edit", methods={"GET"})
     */
    public function edit(Request $r, int $id): Response
    {

        $lecture = $this->getDoctrine()
        ->getRepository(Lecture::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        $lecture_title = $r->getSession()->getFlashBag()->get('lecture_title', []);
        $lecture_description = $r->getSession()->getFlashBag()->get('lecture_description', []);


        return $this->render('lecture/edit.html.twig', [
            'lecture' => $lecture,
            'lecture_title' => $lecture_title[0] ?? '',
            'lecture_description' => $lecture_description[0] ?? '',
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'success' => $r->getSession()->getFlashBag()->get('success', [])
        ]);
    }

    /**
     * @Route("/lecture/update/{id}", name="lecture_update", methods={"POST"})
     */
    public function update(Request $r, $id): Response
    {

        $lecture = $this->getDoctrine()
        ->getRepository(Lecture::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        $lecture
        ->setTitle($r->request->get('lecture_title'))
        ->setDescription($r->request->get('lecture_description'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($lecture);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', $lecture->getTitle().' sekimngai pakeistas.');

        return $this->redirectToRoute('lecture_index');
    }

    /**
     * @Route("/lecture/delete/{id}", name="lecture_delete", methods={"POST"})
     */
    public function delete(Request $r, $id): Response
    {

        $lecture = $this->getDoctrine()
        ->getRepository(Lecture::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        if ($lecture->getGrades()->count() > 0) {
            $r->getSession()->getFlashBag()->add('errors', 'Dalykas pazymetas '.$lecture->getTitle().' negali buti istrintas ('.$lecture->getGrades()->count().' ivertinas pazymiu).');
            return $this->redirectToRoute('lecture_index');
        }

        // remove metodu padauodame ta autoriu ir vykdome
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($lecture);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', $lecture->getTitle().' Sekmingai istrintas');

        return $this->redirectToRoute('lecture_index');
    }
}
