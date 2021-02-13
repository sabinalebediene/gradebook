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

class GradeController extends AbstractController
{
    /**
     * @Route("/grade", name="grade_index")
     */
    public function index(Request $r): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $lectures = $this->getDoctrine()
        ->getRepository(Lecture::class)
        ->findBy([],['title'=>'asc']);

        $students = $this->getDoctrine()
        ->getRepository(Student::class)
        ->findBy([],['surname'=>'asc']);

        // su filtracija
        $grades = $this->getDoctrine()
        ->getRepository(Grade::class);

        if (null !== $r->query->get('student_id')) {
            $grades = $grades->findBy(['student_id' => $r->query->get('student_id')]);
        }

        else {
            $grades = $grades->findAll();
        }

        return $this->render('grade/index.html.twig', [
            'grades' => $grades,
            'students' => $students,
            'lectures' => $lectures,
            // 'studentId' => $r->query->get('student_id') ?? 0
        ]);
    }

    /**
     * @Route("/grade/create", name="grade_create", methods={"GET"})
     */
    public function create(Request $r): Response
    {
        $lectures = $this->getDoctrine()
        ->getRepository(Lecture::class)
        ->findBy([],['title'=>'asc']);

        $students = $this->getDoctrine()
        ->getRepository(Student::class)
        ->findBy([], ['surname' => 'asc']);

        $grade_grade = $r->getSession()->getFlashBag()->get('grade_grade', []);

        return $this->render('grade/create.html.twig', [
            'lectures' => $lectures,
            'students' => $students,
            'grade_grade' => $grade_grade[0] ?? '',
            'grade_student_id' => $grade_student_id[0] ?? '',
            'grade_lecture_id' => $grade_grade_id[0] ?? '',
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    /**
     * @Route("/grade/store", name="grade_store", methods={"POST"})
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {
        $student = $this->getDoctrine()
        ->getRepository(Student::class)
        ->find($r->request->get('grades_student'));

        $lecture = $this->getDoctrine()
        ->getRepository(Lecture::class)
        ->find($r->request->get('grades_lecture'));

        // autoriau validacija, jei jis nepaselectintas
        if(null === $student) {
            $r->getSession()->getFlashBag()->add('errors', 'Pasirink studenta');
        }
        if(null === $lecture) {
            $r->getSession()->getFlashBag()->add('errors', 'Pasirink dalyka');
        }

        $grade = new Grade;
        $grade
        ->setGrade($r->request->get('grade_grade'))
        ->setStudent($student)
        ->setLecture($lecture);

        $errors = $validator->validate($grade);

        // jei yra error, verciame i string ir ji graziname, parodo error'a
        if (count($errors) > 0 || null === $student) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            $r->getSession()->getFlashBag()->add('grade_grade', $r->request->get('grade_grade'));
            $r->getSession()->getFlashBag()->add('grade_student_id', $r->request->get('grade_student_id'));
            $r->getSession()->getFlashBag()->add('grade_student_id', $r->request->get('grade_lecture_id'));
            return $this->redirectToRoute('grade_create');
            
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($grade);
        $entityManager->flush();

        return $this->redirectToRoute('grade_index');
    }

    /**
     * @Route("/grade/edit/{id}", name="grade_edit", methods={"GET"})
     */
    public function edit(int $id): Response
    {
        $grade = $this->getDoctrine()
            ->getRepository(Grade::class)
            ->find($id);

        $lectures = $this->getDoctrine()
            ->getRepository(Lecture::class)
            ->findBy([],['title'=>'asc']);

        $students = $this->getDoctrine()
            ->getRepository(Student::class)
            ->findBy([],['surname'=>'asc']);


        return $this->render('grade/edit.html.twig', [
            'grade' => $grade,
            'lectures' => $lectures,
            'students' => $students,
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    /**
     * @Route("/grade/update/{id}", name="grade_update", methods={"POST"})
     */
    public function update(request $r, $id, ValidatorInterface $validator): Response
    {

        $grade = $this->getDoctrine()
        ->getRepository(Grade::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        $student = $this->getDoctrine()
        ->getRepository(Student::class)
        ->find($r->request->get('grades_student'));

        $lecture = $this->getDoctrine()
        ->getRepository(Lecture::class)
        ->find($r->request->get('grades_lecture'));

        $grade
        ->setGrade($r->request->get('grade_grade'))
        ->setAuthor($student)
        ->setLecture($lecture);

        // atiduoda nauja info
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($grade);
        $entityManager->flush();


        return $this->redirectToRoute('grade_index');
    }

    /**
     * @Route("/grade/delete/{id}", name="grade_delete", methods={"POST"})
     */
    public function delete($id): Response
    {

        $grade = $this->getDoctrine()
        ->getRepository(Grade::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        // remove metodu padauodame ta autoriu ir vykdome
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($grade);
        $entityManager->flush();

        return $this->redirectToRoute('grade_index');
    }
}
