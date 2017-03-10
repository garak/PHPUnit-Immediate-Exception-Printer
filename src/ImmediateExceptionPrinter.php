<?php
namespace ScriptFUSION\PHPUnitImmediateExceptionPrinter;

class ImmediateExceptionPrinter extends \PHPUnit\TextUI\ResultPrinter
{
    /**
     * The exception thrown by the last test.
     *
     * @var \Exception|null
     */
    protected $exception;

    /**
     * PHPUnit built-in progress indication character, e.g. E for error.
     *
     * @var string
     */
    protected $progress;

    /**
     * Last colour used by a progress indicator.
     *
     * @var string
     */
    protected $lastColour;

    private static $performanceThresholds = [
        'fg-red' => 1000,
        'fg-yellow' => 200,
        'fg-green' => 0,
    ];

    public function startTest(\PHPUnit\Framework\Test $test)
    {
        parent::startTest($test);

        $this->exception = $this->progress = null;
        $this->lastColour = 'fg-green,bold';
    }

    protected function writeProgress($progress)
    {
        // Record progress so it can be written later instead of at start of line.
        $this->progress = $progress;

        ++$this->numTestsRun;
    }

    protected function writeProgressWithColor($color, $buffer)
    {
        parent::writeProgressWithColor($color, $buffer);

        $this->lastColour = $color;
    }

    public function endTest(\PHPUnit\Framework\Test $test, $time)
    {
        parent::endTest($test, $time);

        $this->write(sprintf(
            '%3d%% %s ',
            floor($this->numTestsRun / $this->numTests * 100),
            $this->progress
        ));
        $this->writeWithColor($this->lastColour, \PHPUnit\Util\Test::describe($test), false);
        $this->writePerformance($time);

        if ($this->exception) {
            $this->printExceptionTrace($this->exception);
        }
    }

    /**
     * Writes the test performance metric formatted as the number of milliseconds elapsed.
     *
     * @param float $time Number of seconds elapsed.
     */
    protected function writePerformance($time)
    {
        $ms = round($time * 1000);

        foreach (self::$performanceThresholds as $colour => $threshold) {
            if ($ms > $threshold) {
                break;
            }
        }

        $this->writeWithColor($colour, " ($ms ms)");
    }

    protected function printExceptionTrace(\Exception $exception)
    {
        $this->writeNewLine();

        // Parse nested exception trace line by line.
        foreach (explode("\n", $exception) as $line) {
            // Print exception name and message.
            if (!$exception instanceof \PHPUnit\Framework\AssertionFailedError
                && false !== $pos = strpos($line, ': ')
            ) {
                $whitespace = str_repeat(' ', $pos + 2);
                $this->writeWithColor('bg-red,fg-white', $whitespace);

                // Exception name.
                $this->writeWithColor('bg-red,fg-white', sprintf(' %s ', substr($line, 0, $pos)), false);
                // Exception message.
                $this->writeWithColor('fg-red', substr($line, $pos + 1));

                $this->writeWithColor('bg-red,fg-white', $whitespace);

                continue;
            }

            $this->writeWithColor('fg-red', $line);
        }
    }

    /**
     * Called when an exception is thrown in the test runner.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \Exception $e
     * @param float $time
     */
    public function addError(\PHPUnit\Framework\Test $test, \Exception $e, $time)
    {
        $this->writeProgressWithColor('fg-red,bold', 'E');

        $this->exception = $e;
        $this->lastTestFailed = true;
    }

    /**
     * Called when an assertion fails in the test runner.
     *
     * @param \PHPUnit\Framework\Test $test
     * @param \PHPUnit\Framework\AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, $time)
    {
        $this->writeProgressWithColor('fg-red,bold', 'F');

        $this->exception = $e;
        $this->lastTestFailed = true;
    }
}
