<?php

namespace App\Service\RelevantNotes\TfIdfMatrixStrategy;

use App\Entity\Note;
use App\Entity\TermStatistic;
use App\Entity\TfIdfVector;
use App\Repository\NoteRepository;
use App\Repository\TermStatisticRepository;
use App\Repository\TfIdfVectorRepository;
use App\Service\RelevantNotes\FeatureExtraction\TextPreprocessor;
use Doctrine\ORM\EntityManagerInterface;
use Pgvector\Vector;

class TfIdfMatrixService
{
    private const TOP_TERMS_LIMIT = 1000;

    public function __construct(
        private NoteRepository          $noteRepository,
        protected TfIdfVectorRepository $tfIdfVectorRepository,
        private TermStatisticRepository $termStatisticRepository,
        private EntityManagerInterface  $em,
        private TextPreprocessor        $textPreprocessor,
    )
    {
    }

    /**
     * Preprocess the note and update the term frequencies of the note.
     * @param Note $note
     */
    public function preprocessNote(Note $note): void
    {
        $tokens = $this->textPreprocessor->preprocess($note->getTitle() . ' ' . $note->getContent());

        if($note->getTfIdfVector() === null) {
            $tfIdfVector = new TfIdfVector();
            $note->setTfIdfVector($tfIdfVector);
            $this->em->persist($tfIdfVector);
        }
        // Update the term frequencies of the note
        $note->getTfIdfVector()->setTermFrequencies($this->createTermFrequencyMap($tokens));
        $this->em->flush();
    }

    /**
     * Preprocess all notes in the database. Service function.
     * @return void
     */
    public function preprocessAllNotes(): void
    {
        $notes = $this->noteRepository->findAll();
        foreach ($notes as $note) {
            $this->preprocessNote($note);
        }
    }

    /**
     * Update the TF-IDF vectors of all notes in the database.
     * Source: BI-VWM lecture 3 - Vector model of information retrieval (prof. RNDr.Tomáš Skopal, PhD.) https://moodle-vyuka.cvut.cz/pluginfile.php/898824/course/section/133695/BIVWM_lecture03.pdf
     * @return void
     */
    public function updateTfIdfVectors(): void
    {
        $notes = $this->noteRepository->findAll();
        $topTerms = $this->getTopTerms(self::TOP_TERMS_LIMIT);
        $notesCount = count($notes);
        foreach ($notes as $note) {
            $termFrequencyMap = $note->getTfIdfVector()->getTermFrequencies();
            $tfIdfVector = [];
            foreach ($topTerms as $term => $documentFrequency) {
                $termFrequency = $termFrequencyMap[$term] ?? 0;
                $inverseDocumentFrequency = log($notesCount / $documentFrequency);
                $tfIdf = $termFrequency * $inverseDocumentFrequency;
                $tfIdfVector[] = $tfIdf;
            }
            $note->getTfIdfVector()->setVector(new Vector($tfIdfVector));
        }
        $this->em->flush();
    }

    /**
     * Get the top terms from the term statistics table to fit the TF-IDF vectors.
     * There are different strategies to select the top terms.
     * @param int $limit
     * @return array
     */
    private function getTopTerms(int $limit): array
    {
        $termStatistics = $this->termStatisticRepository->findBy([], ['tfIdfValue' => 'DESC'], $limit);
        $topTerms = [];
        foreach ($termStatistics as $termStatistic) {
            $topTerms[$termStatistic->getTerm()] = $termStatistic->getDocumentFrequency();
        }
        return $topTerms;
    }

    /**
     * Create a term frequency (TF) map from an array of tokens.
     * @param array $tokens
     * @return array
     */
    private function createTermFrequencyMap(array $tokens): array
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

        // Calculate the term frequency (TF) for each token
        foreach ($termFrequencyMap as $token => $frequency) {
            $termFrequencyMap[$token] = $frequency / $tokenCount;
        }

        return $termFrequencyMap;
    }

    /**
     * Delete all old term statistics from the database.
     * @return void
     */
    private function deleteOldTermStatistics(): void
    {
        $termStatistics = $this->termStatisticRepository->findAll();
        foreach ($termStatistics as $termStatistic) {
            $this->em->remove($termStatistic);
        }
        $this->em->flush();
    }

    /**
     * Update the document frequency of each term in the term statistics table. For calculating the inverse document frequency (IDF).
     * @return void
     */
    public function updateTermStatistics(): void
    {
        $this->deleteOldTermStatistics();
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

        // Once all document frequencies are updated, calculate the average TF-IDF value for each term across all documents
        foreach ($this->termStatisticRepository->findAll() as $termStatistic) {
            $tfIdfValue = $this->getTfIdfValueForTermByAverage($termStatistic, $notes);
            $termStatistic->setTfIdfValue($tfIdfValue);
        }

        $this->em->flush();
    }

    /**
     * @param TermStatistic $termStatistic
     * @param Note[] $notes
     * @return float
     */
    public function getTfIdfValueForTermByAverage(TermStatistic $termStatistic, array $notes): float
    {
        $sumTfIdf = 0;

        $documentFrequency = $termStatistic->getDocumentFrequency();
        $inverseDocumentFrequency = log(count($notes) / $documentFrequency);

        foreach ($notes as $note) {
            $termFrequencyMap = $note->getTfIdfVector()->getTermFrequencies();
            $termFrequency = $termFrequencyMap[$termStatistic->getTerm()] ?? 0;
            $sumTfIdf += $termFrequency * $inverseDocumentFrequency;
        }

        return $sumTfIdf / count($notes);
    }
}