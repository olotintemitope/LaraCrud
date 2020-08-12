<?php


namespace App\Traits;

trait OutPutWriterTrait
{
    /**
     * @return string
     */
    public function getDoubleTab(): string
    {
        return static::PHP_TAB.static::PHP_TAB;
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
        return PHP_EOL . self::PHP_CRT;
    }

    /**
     * @return string
     */
    public function getTabAndCarriageReturn(): string
    {
        return static::PHP_CRT . static::PHP_TAB;
    }

    /**
     * @param mixed ...$comments
     * @return string
     */
    public function comments(...$comments): string
    {
        $multiLineComments = '';

        foreach ($comments as $lineComment) {
            $multiLineComments .= static::PHP_TAB . ' * ' . $lineComment . PHP_EOL;
        }

        $startOfComment = static::PHP_TAB . '/**' . PHP_EOL;
        $endOfComment = static::PHP_TAB . ' */';

        return $startOfComment . $multiLineComments . $endOfComment;
    }

    /**
     * @return string
     */
    public function getStartTag(): string
    {
        return "<?php" . $this->getEndOfLine();
    }

    /**
     * @return string
     */
    public function getClosingTag(): string
    {
        return $this->getCarriageReturn(). "}";
    }
}
