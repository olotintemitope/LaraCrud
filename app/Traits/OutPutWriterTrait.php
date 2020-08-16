<?php


namespace App\Traits;

trait OutPutWriterTrait
{
    /**
     * @return string
     */
    public function getDoubleTab(): string
    {
        return static::PHP_TAB . static::PHP_TAB;
    }

    /**
     * @return string
     */
    public function getCarriageReturn(): string
    {
        return static::PHP_CRT;
    }

    /**
     * @return string
     */
    protected function getTabAlignment(): string
    {
        return static::PHP_CRT . static::PHP_TAB;
    }

    /**
     * @return string
     */
    protected function getEndOfLine(): string
    {
        return $this->getNewLine() . self::PHP_CRT;
    }

    /**
     * @return string
     */
    public function getTabAndCarriageReturn(): string
    {
        return static::PHP_CRT . static::PHP_TAB;
    }

    /**
     * Write comments to the file
     *
     * @param mixed ...$comments
     * @return string
     */
    public function comments(...$comments): string
    {
        $multiLineComments = '';

        foreach ($comments as $lineComment) {
            $multiLineComments .= $this->writeLine(' * ' . $lineComment, 1);
        }

        $startOfComment = $this->writeLine('/**', 1);
        $endOfComment = $this->writeLine(' */', 1);

        return $startOfComment . $multiLineComments . $endOfComment;
    }

    /**
     * @return string
     */
    public function getStartTag(): string
    {
        return $this->writeLine("<?php", 0);
    }

    /**
     * @return string
     */
    public function getClosingTag(): string
    {
        return  $this->writeLine("}", 0);
    }

    /**
     * @param string $line
     * @param int $tabs
     * @param bool $newLine
     * @param bool $carriageReturn
     * @return string
     */
    public function writeLine(string $line, int $tabs, $newLine = true, $carriageReturn = true): string
    {
        $systemOs = PHP_OS;

        if ($systemOs === 'Windows') {
            return $this->tabIndent($tabs) . $line . ($carriageReturn ? static::PHP_CRT : "") . ($newLine ? $this->getNewLine() : "");
        }
        return $this->tabIndent($tabs) . $line  . ($newLine ? $this->getNewLine() : "");
    }

    /**
     * @param int $times
     * @return string
     */
    protected function tabIndent(int $times): string
    {
        $tabs = "";
        for ($tab = 1; $tab <= $times; $tab++) {
            $tabs .= "\t";
        }

        return $tabs;
    }
}
