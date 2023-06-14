<?php

namespace App\Helpers;

use Exception;
use Stringable;

/**
 * Localisation string Script output, based upon that used by Tightenco\Ziggy
 */
class LocalisationScript implements Stringable
{
    protected string $trans;

    public function __construct(array $trans)
    {
        $this->trans = json_encode($trans);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Unable to encode translation strings to JSON: " . json_last_error_msg());
        }
    }

    public function __toString(): string
    {
        return <<<HTML
<script type="text/javascript">
    const Translations = {$this->trans};
</script>
HTML;
    }
}