<?php

namespace App\Service\RelevantNotes\TfIdfMatrixStrategy;

use App\Entity\Note;
use App\Entity\TermStatistic;
use App\Entity\TfIdfVector;
use App\Repository\NoteRepository;
use App\Repository\TermStatisticRepository;
use App\Repository\TfIdfVectorRepository;
use App\Service\RelevantNotes\FeatureExtraction\TextPreprocessor;
use App\Service\RelevantNotes\SearchStrategyInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pgvector\Vector;

class TfIdfMatrixStrategy implements SearchStrategyInterface
{
    public function __construct(
        private NoteRepository $noteRepository,
        private TfIdfVectorRepository $tfIdfVectorRepository,
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
        return $this->tfIdfVectorRepository->findRelevantNotesByVectorSimilarity($note->getId(), $this->getStrategySql());
    }

    /**
     * @return string
     */
    public function getStrategyMethodName(): string
    {
        return 'TF-IDF Matrix Strategy';
    }

    /**
     * Get the SQL query for the TF-IDF matrix strategy. <=> is the operator for cosine distance.
     * Source: https://github.com/pgvector/pgvector?tab=readme-ov-file#querying (pgvector GitHub documentation)
     * @return string
     */
    public function getStrategySql(): string
    {
        return "
            SELECT 
                *,
                (tf_idf_vector.vector <=> (SELECT vector FROM tf_idf_vector WHERE note_id = :noteId)) AS distance
            FROM tf_idf_vector
            WHERE note_id != :noteId
            ORDER BY distance
            LIMIT 10;
        ";
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
     * Update the TF-IDF vectors of all notes in the database.
     * Source: BI-VWM lecture 3 - Vector model of information retrieval (prof. RNDr.Tomáš Skopal, PhD.) https://moodle-vyuka.cvut.cz/pluginfile.php/898824/course/section/133695/BIVWM_lecture03.pdf
     * @return void
     */
    public function updateTfIdfVectors(): void
    {
        $notes = $this->noteRepository->findAll();
        $topTerms = $this->getTopTerms(100);
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
        $termStatistics = $this->termStatisticRepository->findBy([], ['documentFrequency' => 'DESC'], $limit);
        $topTerms = [];
        foreach ($termStatistics as $termStatistic) {
            $topTerms[$termStatistic->getTerm()] = $termStatistic->getDocumentFrequency();
        }
        return $topTerms;
    }

    /*public function tempCreateTfIdfVectors(): void
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
    }*/

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
    }
}