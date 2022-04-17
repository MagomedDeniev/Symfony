<?php


namespace App\Validator\Constraints;

use App\Entity\Song;
use App\Repository\SongRepository;
use Cocur\Slugify\Slugify;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class UniqueSongValidator extends ConstraintValidator
{
    public function __construct(
        private SongRepository $repo,
        private TranslatorInterface $translator
    ){}

    public function validate($entity, Constraint $constraint)
    {
        $slugifier  = new Slugify();
        $vocalist     = '';
        $message    = '';

        if ($entity->getVocalist()){
            $vocalist = $entity->getVocalist()->getFullName() . ' ';
        }

        $slug       = $slugifier->slugify($vocalist . $entity->getTitle());
        $exist      = $this->repo->findOneBy(['slug' => $slug]);
        $duplicate  = $this->duplicate($entity);

        if ($duplicate) {
            $message   = $this->translator->trans('person.already.has.song.with.same.title');
        } elseif ($exist) {
            $message   = $this->translator->trans('already.have.song.with.same.slug');
            $duplicate = true;
        }

        /** Verify if this is an existing song entity, for allow update */
        if ($this->repo->findOneBy(['id' => $entity->getId()])){
            if ($exist && $exist->getId() == $entity->getId()) {
                $duplicate = false;
            }
        }

        if ($duplicate) {
            $this->context  ->buildViolation($constraint->message)
                            ->atPath('title')
                            ->setParameter('{{ message }}', $message)
                            ->addViolation();
        }
    }

    public function duplicate(Song $newSong)
    {
        $songs     = $this->repo->findBy(['title' => $newSong->getTitle()]);
        $bool       = false;

        foreach ($songs as $song) {
            /** Verify if vocalist exists to compare with others in database */
            if ($newSong->getVocalist()){
                $fullname = false;
                $same     = false;

                if ($song->getVocalist()) {
                    $fullname = $newSong->getVocalist()->getFullName() == $song->getVocalist()->getFullName();
                    $same = $newSong->getId() !== $song->getId();
                }

                if ($fullname && $same) {
                    $bool = true;
                }
            }
        }

        return $bool;
    }
}
