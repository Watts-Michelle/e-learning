<?php

require_once BASE_PATH . '/vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
require_once BASE_PATH . '/vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';

class QuizImporter extends BetterBulkLoader
{
	private $quiz;

	public $columnMap = array(
		'Question' => 'Question'
	);

	/*
 * Load the given file via {@link self::processAll()} and {@link self::processRecord()}.
 * Optionally truncates (clear) the Hook and Hook_Genres tables before it imports.
 *
 * @return BulkLoader_Result See {@link self::processAll()}
 */
	public function load($filepath = null) {
		increase_time_limit_to(3600);
		increase_memory_limit_to('512M');

		if (! $filepath) {
			$filepath = $this->source->getFilePath();
		}

		$extension = File::get_file_extension($filepath);

		if ($extension == 'csv') {
			return $this->processAll($filepath);
		} elseif ($extension == 'xls' || $extension == 'xlsx')  {
			return $this->processExcelQuiz($filepath);
		}
	}

	public function setQuiz(Quiz $quiz)
	{
		$this->quiz = $quiz;
	}

	/**
	 * @param array $record
	 * @param array $columnMap
	 * @param BulkLoader_Result $results
	 * @param bool $preview
	 * @return mixed
	 * @throws Exception
	 */
	protected function processRecord($record, $columnMap, &$results, $preview = false) {

		if (! $this->quiz instanceof Quiz) throw new Exception('Wrong class used');

		foreach ($record as $key => $value) {
			$record[$key] = trim($value);
		}

		if (empty($record['Question'])) return false;

		$question = $this->quiz->Questions()->filter(['Title' => $record['Question']])->first();

		if (! $question) {
			$question = new Question();
			$question->QuizID = $this->quiz->ID;
			$question->Title = $record['Question'];

			if (preg_match('/(\$\$.*){2}/', $record['Question'])) {
				$question->FullQuestion = '<p>' . $record['Question'] . '</p>';
			}

			$question->write();
		} else {
			$answers = $question->Answers();

			foreach ($answers as $answer) {
				$answer->delete();
			}
		}

		$correct = preg_grep('/(?i)^Correct/', array_keys($record));
		$incorrect = preg_grep('/(?i)^Incorrect/', array_keys($record));
		$correctCount = 0;

		foreach ($correct as $correctKey) {
			if ($record[$correctKey]) {
				$answer = new Answer;
				$answer->QuestionID = $question->ID;
				$answer->Title = $record[$correctKey];

				if (preg_match('/(\$\$.*){2}/', $record[$correctKey])) {
					$answer->FullAnswer = '<p>' . $record[$correctKey] . '</p>';
				}

				$answer->IsCorrect = 1;
				$answer->write();

				$correctCount++;
			}
		}

		if ($correctCount > 1) {
			$question->AcceptMultipleAnswers = 1;
			$question->write();
		}

		foreach ($incorrect as $incorrectKey) {
			if ($record[$incorrectKey]) {
				$answer = new Answer;
				$answer->QuestionID = $question->ID;
				$answer->Title = $record[$incorrectKey];

				if (preg_match('/(\$\$.*){2}/', $record[$incorrectKey])) {
					$answer->FullAnswer = '<p>' . $record[$incorrectKey] . '</p>';
				}

				$answer->IsCorrect = 0;
				$answer->write();
			}
		}

		return $question->ID;
	}

	/**
	 * Given an excel sheet, create an array for each row and process the same way a CSV row would be
	 * @param $filepath
	 * @return BetterBulkLoader_Result
	 */
	private function processExcelQuiz($filepath)
	{
		$objPHPExcel = PHPExcel_IOFactory::load($filepath);
		$sheet = $objPHPExcel->getSheet(0);
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

		$question = [];

		$keys = ['Question', 'Correct 1', 'Correct 2', 'Correct 3', 'Incorrect 1', 'Incorrect 2', 'Incorrect 3'];

		//create array of column values assigned against each $key for each row
		for ($row = 2; $row <= $highestRow; ++ $row) {

			for ($col = 0; $col < $highestColumnIndex; ++ $col) {
				$cell = $sheet->getCellByColumnAndRow($col, $row);

				if ($col < count($keys)) {
					$question[$row][$keys[$col]] = $cell->getValue();

					if ($question[$row][$keys[$col]] === false) {
						$question[$row][$keys[$col]] = 'false';
					} elseif ($question[$row][$keys[$col]] === true) {
						$question[$row][$keys[$col]] = 'true';
					}
				}
			}
		}

		$results = new BetterBulkLoader_Result();

		foreach ($question as $record) {
			$this->processRecord($record, $this->columnMap, $results);
		}

		return $results;
	}
}