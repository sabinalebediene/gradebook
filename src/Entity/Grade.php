<?php

namespace App\Entity;

use App\Repository\GradeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GradeRepository::class)
 */
class Grade
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $grade;

    /**
     * @ORM\Column(type="integer")
     */
    private $lecture_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $student_id;

    /**
     * @ORM\ManyToOne(targetEntity=Student::class, inversedBy="grades")
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity=Lecture::class, inversedBy="grades")
     */
    private $lecture;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGrade(): ?int
    {
        return $this->grade;
    }

    public function setGrade(int $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function getLectureId(): ?int
    {
        return $this->lecture_id;
    }

    public function setLectureId(int $lecture_id): self
    {
        $this->lecture_id = $lecture_id;

        return $this;
    }

    public function getStudentId(): ?int
    {
        return $this->student_id;
    }

    public function setStudentId(int $student_id): self
    {
        $this->student_id = $student_id;

        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getLecture(): ?Lecture
    {
        return $this->lecture;
    }

    public function setLecture(?Lecture $lecture): self
    {
        $this->lecture = $lecture;

        return $this;
    }
}
