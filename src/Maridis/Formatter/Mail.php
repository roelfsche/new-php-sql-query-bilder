<?php
namespace App\Maridis\Formatter;

use Monolog\Formatter\HtmlFormatter;

class Mail extends HtmlFormatter
{
    private $strTitle = '';
    /**
     * Create a HTML h1 tag
     *
     * @param  string $title Text to be in the h1
     * @param  int    $level Error level
     * @return string
     */
    protected function addTitle($title, $level)
    {

        $title = htmlspecialchars($title, ENT_NOQUOTES, 'UTF-8');

        return '<h1 style="background: ' . $this->logLevels[$level] . ';color: #ffffff;padding: 5px;" class="monolog-output">' . $title . '</h1>';
    }
    /**
     * Creates an HTML table row
     *
     * @param  string $th       Row header content
     * @param  string $td       Row standard cell content
     * @param  bool   $escapeTd false if td content must not be html escaped
     * @return string
     */
    protected function addRow($th, $td = ' ', $escapeTd = true)
    {
        $th = htmlspecialchars($th, ENT_NOQUOTES, 'UTF-8');
        if ($escapeTd) {
            $td = '<pre>' . htmlspecialchars($td, ENT_NOQUOTES, 'UTF-8') . '</pre>';
        }

        return "<tr style=\"padding: 4px;text-align: left;\">\n<th style=\"vertical-align: top;background: #ccc;color: #000\" width=\"100\">$th:</th>\n<td style=\"padding: 4px;text-align: left;vertical-align: top;background: #eee;color: #000\">" . $td . "</td>\n</tr>";
    }

    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record)
    {

        $output = '<table cellspacing="1" width="100%" class="monolog-output">';
        $output .= "<tr style=\"padding: 4px;text-align: left;\">\n<th style=\"vertical-align: top; padding-left: 20px;padding-top: 3px;background: " . $this->logLevels[$record['level']] . ";color: #000\" width=\"100\">" . $record['level_name'] . "</th><td style=\"padding: 4px;text-align: left;vertical-align: top;background: #eee;color: #000\">" . $record['datetime']->format($this->dateFormat) . ": " . (string) $record['message'] . "</td>\n</tr>";

        // $output = $this->addTitle($record['level_name'], $record['level']);
        // $output .= '<table cellspacing="1" width="100%" class="monolog-output">';

        // $output .= $this->addRow('Message', (string) $record['message']);
        // $output .= $this->addRow('Time', $record['datetime']->format($this->dateFormat));
        // $output .= $this->addRow('Channel', $record['channel']);
        if ($record['context']) {
            $embeddedTable = '<table cellspacing="1" width="100%">';
            foreach ($record['context'] as $key => $value) {
                $embeddedTable .= $this->addRow($key, $this->convertToString($value));
            }
            $embeddedTable .= '</table>';
            $output .= $this->addRow('Context', $embeddedTable, false);
        }
        if ($record['extra']) {
            $embeddedTable = '<table cellspacing="1" width="100%">';
            foreach ($record['extra'] as $key => $value) {
                $embeddedTable .= $this->addRow($key, $this->convertToString($value));
            }
            $embeddedTable .= '</table>';
            $output .= $this->addRow('Extra', $embeddedTable, false);
        }

        return $output . '</table>';
    }
}
