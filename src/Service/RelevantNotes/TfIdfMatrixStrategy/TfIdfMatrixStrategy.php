<?php

namespace App\Service\RelevantNotes\TfIdfMatrixStrategy;

use App\Entity\Note;
use App\Entity\TermStatistic;
use App\Entity\TfIdfVector;
use App\Repository\NoteRepository;
use App\Repository\TermStatisticRepository;
use App\Service\RelevantNotes\FeatureExtraction\TextPreprocessor;
use App\Service\RelevantNotes\SearchStrategyInterface;
use Doctrine\ORM\EntityManagerInterface;

class TfIdfMatrixStrategy implements SearchStrategyInterface
{
    public function __construct(
        private NoteRepository $noteRepository,
        private TermStatisticRepository $termStatisticRepository,
        private EntityManagerInterface $em,
        private TextPreprocessor $textPreprocessor,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function findRelevantNotes(Note $note): array
    {
        // TODO: Implement findRelevantNotes() method.
        return [];
    }

    /**
     * @param Note $note
     */
    public function preprocessNote(Note $note): void
    {
        $tokens = $this->textPreprocessor->preprocess($note->getTitle() . ' ' . $note->getContent());
        $note->getTfIdfVector()->setTermFrequencies($this->createTermFrequencyMap($tokens));
        $this->updateTermStatistics(); // global update of term statistics
    }

    public function tempCreateTfIdfVectors(): void
    {
        $notes = $this->noteRepository->findAll();
        foreach ($notes as $note) {
            $tokens = $this->textPreprocessor->preprocess($note->getTitle() . ' ' . $note->getContent());
            $termFrequencyMap = $this->createTermFrequencyMap($tokens);
            $tfIdfVector = new TfIdfVector();
            $tfIdfVector->setTermFrequencies($termFrequencyMap);
            $note->setTfIdfVector($tfIdfVector);
            $this->em->persist($note);
        }
        $this->em->flush();
    }

    public function createTermFrequencyMap(array $tokens): array
    {
        $tokenCount = count($tokens);
        $termFrequencyMap = [];
        foreach ($tokens as $token) {
            if (!array_key_exists($token, $termFrequencyMap)) {
                $termFrequencyMap[$token] = 1;
            } else {
                $termFrequencyMap[$token]++;
            }
        }

        foreach ($termFrequencyMap as $token => $frequency) {
            $termFrequencyMap[$token] = $frequency / $tokenCount;
        }

        return $termFrequencyMap;
    }

    public function updateTermStatistics(): void
    {
        // todo: implement deleting the old term statistics
        $notes = $this->noteRepository->findAll();

        foreach ($notes as $note) {
            $termFrequencyMap = $note->getTfIdfVector()->getTermFrequencies();
            foreach ($termFrequencyMap as $term => $frequency) {
                $termStatistic = $this->termStatisticRepository->findOneBy(['term' => $term]);
                if ($termStatistic === null) {
                    $termStatistic = new TermStatistic($term, 1);
                } else {
                    $termStatistic->setDocumentFrequency($termStatistic->getDocumentFrequency() + 1);
                }
                $this->em->persist($termStatistic);
            }
            $this->em->flush();
        }
    }
}