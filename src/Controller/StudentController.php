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


class StudentController extends AbstractController
{
    /**
     * @Route("/student", name="student_index", methods={"GET"})
     */
    public function index(Request $r): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $students = $this->getDoctrine()
        ->getRepository(Student::class);

        if ('name_az' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $students = $students->findBy([],['name'=>'asc']);
        }

        elseif ('name_za' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $students = $students->findBy([],['name'=>'desc']);
        }

        elseif ('surname_az' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $students = $students->findBy([],['surname'=>'asc']);
        }

        elseif ('surname_za' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $students = $students->findBy([],['surname'=>'desc']);
        }
        
        else {
            $students = $students->findAll();
        }
        

        return $this->render('student/index.html.twig', [
            'students' => $students,
            'sortBy' => $r->query->get('sort') ?? 'default',
        ]);
    }

    /**
     * @Route("/student/create", name="student_create", methods={"GET"})
     */
    public function create(Request $r): Response
    {
        $student_name = $r->getSession()->getFlashBag()->get('student_name', []);
        $student_surname = $r->getSession()->getFlashBag()->get('student_surname', []);
        $student_email = $r->getSession()->getFlashBag()->get('student_email', []);
        $student_phone = $r->getSession()->getFlashBag()->get('student_phone', []);

        return $this->render('student/create.html.twig', [
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'student_name' => $student_name[0] ?? '',
            'student_surname' => $student_surname[0] ?? '',
            'student_email' => $student_email[0] ?? '',
            'student_phone' => $student_phone[0] ?? ''
        ]);
    }

    /**
     * @Route("/student/store", name="student_store", methods={"POST"})
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {

        $student = new Student;

        $student->
        setName($r->request->get('student_name'))->
        setSurname($r->request->get('student_surname'))->
        setEmail($r->request->get('student_email'))->
        setPhone($r->request->get('student_phone'));


        $errors = $validator->validate($student);

        // jei yra error, verciame i string ir ji graziname, parodo error'a
        if (count($errors) > 0) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            // klaidos atveju ivestas vardas ir pavarde lieka
            $r->getSession()->getFlashBag()->add('student_name', $r->request->get('student_name'));
            $r->getSession()->getFlashBag()->add('student_surname', $r->request->get('student_surname'));
            $r->getSession()->getFlashBag()->add('student_email', $r->request->get('student_email'));
            $r->getSession()->getFlashBag()->add('student_phone', $r->request->get('student_phone'));
            
            return $this->redirectToRoute('student_create');
            // po rederektinimo pereiname prie create ir ten persiduodam autoriaus name ir surname kintamuosius
        }


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($student);
        $entityManager->flush();

        return $this->redirectToRoute('student_index');
    }

    /**
     * @Route("/student/edit/{id}", name="student_edit", methods={"GET"})
     */
    public function edit(Request $r, int $id): Response
    {
        $student = $this->getDoctrine()
        ->getRepository(Student::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        $student_name = $r->getSession()->getFlashBag()->get('student_name', []);
        $student_surname = $r->getSession()->getFlashBag()->get('student_surname', []);
        $student_email = $r->getSession()->getFlashBag()->get('student_email', []);
        $student_phone = $r->getSession()->getFlashBag()->get('student_phone', []);


        return $this->render('student/edit.html.twig', [
            'student' => $student, // perduodame
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'student_name' => $student_name[0] ?? '',
            'student_surname' => $student_surname[0] ?? '',
            'student_email' => $student_email[0] ?? '',
            'student_phone' => $student_phone[0] ?? ''
        ]);
    }

    /**
     * @Route("/student/update/{id}", name="student_update", methods={"POST"})
     */
    public function update(Request $r, ValidatorInterface $validator, $id): Response
    {
        $student = $this->getDoctrine()
        ->getRepository(Student::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        $student->
        setName($r->request->get('student_name'))->
        setSurname($r->request->get('student_surname'))->
        setEmail($r->request->get('student_email'))->
        setPhone($r->request->get('student_phone'));

        $errors = $validator->validate($student);

        // jei yra error, verciame i string ir ji graziname, parodo error'a
         if (count($errors) > 0) {
 
             foreach($errors as $error) {
                 $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
             }
 
             // klaidos atveju ivestas vardas ir pavarde lieka
             $r->getSession()->getFlashBag()->add('student_name', $r->request->get('student_name'));
             $r->getSession()->getFlashBag()->add('student_surname', $r->request->get('student_surname'));
             $r->getSession()->getFlashBag()->add('student_email', $r->request->get('student_email'));
             $r->getSession()->getFlashBag()->add('student_phone', $r->request->get('student_phone'));
 
             // kai redirectiname i edit, cia yra id, todel turime cia dar perduoti ir nurodyti id, todel klaida paprase cia idet
             return $this->redirectToRoute('student_edit',['id'=>$student->getId()]);
         }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($student);
        $entityManager->flush();

        return $this->redirectToRoute('student_index');
    }

    /**
     * @Route("/student/delete/{id}", name="student_delete", methods={"POST"})
     */
    public function delete($id): Response
    {
        $student = $this->getDoctrine()
        ->getRepository(Student::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas


        // remove metodu padauodame ta autoriu ir vykdome
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($student);
        $entityManager->flush();

        return $this->redirectToRoute('student_index');
    }
}

