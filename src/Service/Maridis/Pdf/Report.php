<?php

namespace App\Service\Maridis\Pdf;

use App\Exception\MscException;
use App\Kohana\Arr;
use Psr\Container\ContainerInterface;
use TCPDF;

class Report extends TCPDF
{
    /**
     * default-report config
     * @var array
     */
    protected $arrReportConfig = null;
    /**
     * pdf-config für den REport-Typ (gemerged)
     * @var array
     */
    protected $arrConfig = null;

    protected $objContainer = null;
    /**
     *
     */
    public function __construct(ContainerInterface $objContainer)
    {
        parent::__construct();

        $this->objContainer = $objContainer;

        $this->arrReportConfig = $objContainer->getParameter('reports');
        // $this->arrConfig = $this->arrReportConfig['pdf']['default'];
        switch (get_class($this)) {
            case "App\Service\Maridis\Pdf\Report\Engine":
            default:
                // $this->arrConfig = $this->arrReportConfig['pdf']['engine'] + $this->arrReportConfig['pdf']['default'];
                $this->arrConfig = Arr::merge($this->arrReportConfig['pdf']['default'], $this->arrReportConfig['pdf']['engine']);
                break;
        }

        $arrMargins = $this->getConfigValue('page.margins');
        $this->SetMargins($arrMargins['content']['left'], $arrMargins['content']['top'], $arrMargins['content']['right']);
        $this->SetHeaderMargin($arrMargins['header']);
        $this->SetFooterMargin($arrMargins['footer']);
        $this->SetAutoPageBreak(true, $arrMargins['content']['bottom']);
    }

    /**
     * Diese methode fügt einen Tabellenkopf dem Dokument hinzu.
     * Die Config für die Zellen siehe config/report.php -> pdf.default.table.header_cell
     *
     * @param array $arrColumns - array('row1', 'row2', ...)
     * @param array $arrWidht   - Array mit den prozentualen Breiten der Spalten
     *                          wenn angegeben, dann muss die Anzahl der Einträge der Anzahl der Spalten entsprechen
     *                          ausserdem muss dann die Summe 100 ergeben
     *                          array(10, 20, 30, 40)
     */
    public function setTableHead($arrColumns, $arrWidth = null)
    {

        $arrConfig = $this->getConfigValue('table.header_cell');

        // Breite des PDF-Blattes
        $intContentSize = $this->getPageWidth() - $this->getConfigValue('page.margins.content.left') - $this->getConfigValue('page.margins.content.right');

        // Breitenarray nicht mit angegeben?
        if (!$arrWidth) {
            // nur wenn für die akt. Tabelle noch nicht gesetzt
            if (!$this->arrTableWidth) {
                // dann Spaltenbreite aus der Config
                if ($arrConfig['w']) {
                    $intColumnWidth = $arrConfig['w'];
                } else {
                    // oder ermitteln (Breite des Blatts / Anzahl Spalten)
                    $intColumnWidth = $intContentSize / count($arrColumns);
                }
                $arrWidth = array_fill(0, count($arrColumns), $intColumnWidth);
            }
        } else {
            // checke das Breitenarray auf inh. Korrektheit
            if (count($arrWidth) != count($arrColumns)) {
                throw new MscException('Breitendefinition passt nicht zur Spaltendefinition: Anzahl-Breiten = :width_count Anzahl Spalten = :column_count', array(
                    ':width_count' => count($arrWidth),
                    ':column_count' => count($arrColumns),
                ));
            }
            // check auf 100%
            $intSum = array_reduce($arrWidth, function ($intSum, $intWidth) {
                return $intSum + $intWidth;
            }, 0);
            if ($intSum != 100) {
                throw new MscException('Summe der Spaltenbreiten != 100; Summe = :sum', array(
                    ':sum' => $intSum,
                ));
            }
        }

        // nur wenn Breitenarray übergeben bzw. aus den defaults geholt
        // und für diese Tabelle noch nicht gemerkt...
        // ---
        // 2. Bed. rausgenommen, weil sonst Fehler, wenn ich die 2. Tabelle dem Dokument hinzufüge
        if ($arrWidth) // && !$this->arrTableWidth)
        {
            // wandle die prozentualen Werte nun in abs. mm um
            foreach ($arrWidth as $intIndex => $intColumnWidth) {
                $arrWidth[$intIndex] = $intColumnWidth * $intContentSize / 100;
            }
            $this->arrTableWidth = $arrWidth;
        }

        // merken
        $this->arrTableHead = $arrColumns;

        $intMaxCellHeight = $arrConfig['h'];
        if (!$intMaxCellHeight) {
            $arrTmpConfig = $arrConfig;
            foreach ($arrColumns as $intIndex => $strColumn) {
//                $intMaxCellHeight = max($intMaxCellHeight, $this->getEstimatedCellHeight($arrWidth[$intIndex], 6, $strColumn, $arrConfig['border'], 'M', 0));
                //                $arrTmpConfig['w'] = $arrWidth[$intIndex];
                $arrTmpConfig['w'] = $this->arrTableWidth[$intIndex];
                $intMaxCellHeight = max($intMaxCellHeight, $this->getEstimatedCellHeight($strColumn, $arrTmpConfig));

            }
        }

        // Farben und Linien, Font
        //        $this->setFillColor($arrConfig['fill_color_red'], $arrConfig['fill_color_green'], $arrConfig['fill_color_blue']);
        //        $this->setTextColor($arrConfig['text_color_red'], $arrConfig['text_color_green'], $arrConfig['text_color_blue']);
        //        $this->setDrawColor($arrConfig['draw_color_red'], $arrConfig['draw_color_green'], $arrConfig['draw_color_blue']);
        //        $this->SetLineWidth($arrConfig['line_width']);
        //
        //        $this->SetFont($arrConfig['font_family'], $arrConfig['font_style'], $arrConfig['font_size']);
        foreach ($arrColumns as $intIndex => $strColumn) {
            $arrTmpConfig['w'] = $this->arrTableWidth[$intIndex];
//            $arrTmpConfig['w'] = $arrWidth[$intIndex];
            $arrTmpConfig['h'] = $intMaxCellHeight;
            $this->addCell($strColumn, $arrTmpConfig);
        }
        $this->Ln();
        // @todo: muss den Border berücksichtigen!!!! sicherlich überall dann immer!!!
        if (Arr::get($arrConfig, 'line_width') /* suche nach 'B' im Border */) {
            $this->addY(Arr::get($arrConfig, 'line_width'));
        }
    }

    public function addHeadline($strContent, $strType = 'h1')
    {
        $arrConfig = $this->getConfigValue($strType);
        $this->addCell($strContent, $arrConfig);
    }

    public function addText($strContent)
    {
        $arrConfig = $this->getConfigValue('text');
        $this->addCell($strContent, $arrConfig);
    }

    /**
     * Diese Methode fügt dem Dokument eine Tabellenzeile hinzu.
     * Sie checkt vorher, ob genügend Platz auf der PDF-Seite vorhanden ist. Wenn nicht, kann sie optional einen
     * Seitenumbruch machen und optional den Tabellenkopf nochmal mit ausgeben.
     *
     * Ihr kann eine Config für alle Zellen übergeben werden (Parameter $mixedConfig) oder aber jeder einzelnen Zelle eine eigene (siehe unten param $arrColumns).
     *
     * Wenn keine Config angegebene, dann zieht die default config siehe config/report.php -> pdf.default.table.cell
     * Wenn nicht genug Platz auf der Seite ist, wird die Zeile nicht gedruckt und FALSE zurückgegeben.
     *
     * @param array      $arrColumns             - array('row1', array('value' => 'row2', 'config' => array(...)) , ...)
     * @param mixed      $mixedConfig            - string|array
     *                                           wenn String, dann Pfad zu Array innerhalb der Report-Config: 'table.odd'
     * @param bool|FALSE $boolAutoNewPage        - wenn TRUE, wird eine neue Seite angelegt, wenn zu wenig Platz
     * @param bool|TRUE  $boolRepeatTableHeader  - wenn neue Seite implizit angelegt, dann wird der Tabellenkopf mit ausgegeben, wenn TRUE
     * @param null       $mixedAlternativeConfig - alternative Zeilenconfig, für den Fall, dass neue Seite angelegt wird
     *                                           (sinnvoll, bspw. um odd/even immer mit odd zu starten)
     * @return bool - wenn TRUE, passte die Zeile noch auf die Seite
     *                                           - wenn FALSE, dann - je nach config - wurde neue Seite angelegt und Zeile gedruckt oder nicht
     */
    public function addTableRow($arrColumns, $mixedConfig = null, $boolAutoNewPage = false, $boolRepeatTableHeader = true, $mixedAlternativeConfig = null)
    {
        $boolReturn = true;
        if (!$this->arrTableWidth) {
            throw new MscException('Tabellenspalte ohne Tabellenkopf kann nicht gedruckt werden');
        }

        if (count($arrColumns) != count($this->arrTableWidth)) {
            throw new MscException('Breitendefinition passt nicht zur Spaltendefinition: Anzahl-Breiten = :width_count Anzahl Spalten = :column_count', array(
                ':width_count' => count($this->arrTableWidth),
                ':column_count' => count($arrColumns),
            ));
        }

        // Config für Zeile
        $arrRowConfig = $this->compileConfig($this->getConfigValue('table.cell'), $mixedConfig);

        // errechne die Höhe für die gesamte Zeile
        $intMaxCellHeight = Arr::get($arrRowConfig, 'h', 0);
        if (!$intMaxCellHeight) {
            foreach ($arrColumns as $intIndex => $mixedColumn) {
                if (is_array($mixedColumn)) {
                    $strColumn = Arr::get($mixedColumn, 'value', '');
                    $arrCellConfig = $this->compileConfig($arrRowConfig, Arr::get($mixedColumn, 'config', array()));
                } else {
                    $strColumn = $mixedColumn;
                    $arrCellConfig = $arrRowConfig;
                }

                $arrCellConfig['w'] = $this->arrTableWidth[$intIndex];
                $intMaxCellHeight = max($intMaxCellHeight, $this->getEstimatedCellHeight($strColumn, $arrCellConfig));
            }
        }

        // wenn nicht genug Platz, dann nicht drucken
        // aufrufende Methode kann dann addPage() aufrufen und bspw. neue config übergeben (damit erste Zeile bspw. immer mit Hintergrund)
        if ($this->getPageHeight() - ($this->GetY() + Arr::get($this->getMargins(), 'bottom', 50) + $intMaxCellHeight) < 0) {
            if ($boolAutoNewPage) {
                $this->AddPage();
                if ($boolRepeatTableHeader) {
                    $this->setTableHead($this->arrTableHead); //, $this->arrTableWidth);
                    // wenn eine Config für diesen Fal übermittelt wurde, dann die auch nehmen
                    if ($mixedAlternativeConfig) {
                        $arrRowConfig = $this->compileConfig($this->getConfigValue('table.cell'), $mixedAlternativeConfig);
                    }
                }
                $boolReturn = false;
            } else {
                return false;
            }
        }

        foreach ($arrColumns as $intIndex => $mixedColumn) {
            if (is_array($mixedColumn)) {
                $strColumn = Arr::get($mixedColumn, 'value', '');
                $arrCellConfig = $this->compileConfig($arrRowConfig, Arr::get($mixedColumn, 'config', array()));
            } else {
                $strColumn = $mixedColumn;
                $arrCellConfig = $arrRowConfig;
            }

            $arrCellConfig['w'] = $this->arrTableWidth[$intIndex];
            $arrCellConfig['h'] = $intMaxCellHeight;
            $this->addCell($strColumn, $arrCellConfig);
        }
        $this->Ln();

        return $boolReturn;
    }

    /**
     * interne Methode, die letztendlich eine Zelle mit margin, linewidth, color usw. schreibt
     *
     * @param string $strContent - der Inhalt
     * @param array  $arrConfig  - die Config
     */
    public function addCell($strContent, $arrConfig)
    {
        // margins
        $arrOldPaddings = $this->getCellPaddings();
        $arrPaddings = Arr::extract($arrConfig, array('padding_left', 'padding_top', 'padding_right', 'padding_bottom'));
        if ($arrPaddings && count($arrPaddings) == 4 && $arrPaddings['padding_left'] && $arrPaddings['padding_top'] && $arrPaddings['padding_right'] && $arrPaddings['padding_bottom']) {
            call_user_func_array(array($this, 'setCellPaddings'), $arrPaddings);
        }

        $arrOldFillColor = $this->FillColor;
        $arrFillColor = Arr::extract($arrConfig, array('fill_color_red', 'fill_color_green', 'fill_color_blue'));
        if ($arrFillColor && count($arrFillColor) == 3) {
            $this->setFillColor($arrConfig['fill_color_red'], $arrConfig['fill_color_green'], $arrConfig['fill_color_blue']);
        }

        // Text-Color
        $arrOldTextColor = $this->TextColor;
        $arrTextColor = Arr::extract($arrConfig, array('text_color_red', 'text_color_green', 'text_color_blue'));
        if ($arrTextColor && count($arrTextColor) == 3) {
            $this->setTextColor($arrConfig['text_color_red'], $arrConfig['text_color_green'], $arrConfig['text_color_blue']);
        }

        // Draw-Color (Zeichenfarbe Striche)
        $strOldDrawColor = $this->DrawColor;
        $arrDrawColor = Arr::extract($arrConfig, array('draw_color_red', 'draw_color_green', 'draw_color_blue'));
        if ($arrDrawColor && count($arrDrawColor) == 3) {
            $this->setDrawColor($arrConfig['draw_color_red'], $arrConfig['draw_color_green'], $arrConfig['draw_color_blue']);
        }

        // Linienstärke
        $floatOldLineWidth = $this->LineWidth;
        if (Arr::get($arrConfig, 'line_width')) {
            $this->SetLineWidth($arrConfig['line_width']);
        }

        // Font
        $arrOldFont = array(
            $this->getFontFamily(),
            $this->getFontStyle(),
            $this->getFontSizePt(),
        );

        $arrFontConfig = Arr::extract($arrConfig, array('font_family', 'font_style', 'font_size'));
        if ($arrFontConfig && count($arrFontConfig) == 3) {
            $this->SetFont($arrConfig['font_family'], $arrConfig['font_style'], $arrConfig['font_size']);
        }

        // margin-top
        if (Arr::get($arrConfig, 'margin_top')) {
            $this->addY(Arr::get($arrConfig, 'margin_top'));
        }
//         Text drucken
        $this->MultiCell($arrConfig['w'],
            $arrConfig['h'],
            $strContent,
            $arrConfig['border'],
            $arrConfig['align'],
            $arrConfig['fill'],
            $arrConfig['ln'],
            $arrConfig['x'],
            $arrConfig['y'],
            $arrConfig['reseth'],
            $arrConfig['stretch'],
            $arrConfig['ishtml'],
            $arrConfig['autopadding'],
            $arrConfig['maxh'],
            $arrConfig['valign'],
            $arrConfig['fitcell']
        );

        // margin-bottom
        if (Arr::get($arrConfig, 'margin_bottom')) {
            $this->addY(Arr::get($arrConfig, 'margin_bottom'));
        }
        // fill_color
        $this->FillColor = $arrOldFillColor;
        // text color
        $this->TextColor = $arrOldTextColor;
// draw color
        $this->DrawColor = $strOldDrawColor;
        // line width
        $this->LineWidth = $floatOldLineWidth;
// Font
        call_user_func_array(array($this, 'SetFont'), $arrOldFont);
        // margin zurücksetzen
        call_user_func_array(array($this, 'setCellPaddings'), $arrOldPaddings);
    }

    /**
     * fügt einen "Absatz" ein
     *
     * @param            $intY
     * @param bool|TRUE  $boolResetX
     * @param bool|FALSE $boolRtlOff
     */
    public function addY($intY, $boolResetX = true, $boolRtlOff = false)
    {
        if ($intY) {
            $this->SetY($this->GetY() + $intY, $boolResetX, $boolRtlOff);
        }
    }

    /**
     * merged zwei Config-Arrays zusammen, wobei das zweite das erste überschreibt
     * Der zweite Parameter ist entweder das Array oder der Pfad zur Config innerhalb dieser Report-Config
     *
     * @param array        $arrConfig   - config array
     * @param array|string $mixedConfig - config-array oder Pfad innerhalb der report-config
     * @return array
     */
    protected function compileConfig($arrConfig, $mixedConfig)
    {
        if ($mixedConfig && (is_string($mixedConfig) || is_array($mixedConfig))) {
            if (is_string($mixedConfig)) {
                $mixedConfig = $this->getConfigValue($mixedConfig);
            }

            $arrConfig = Arr::merge($arrConfig, $mixedConfig);
        }
        return $arrConfig;
    }

    /**
     *
     * Aus der Doku/Quelltext von TCPDF kopiert.
     * Liefert die exakte Höhe einer Zelle (Multicell) zurück.
     * @param int        $w
     * @param int        $h
     * @param            $txt
     * @param int        $border
     * @param string     $align
     * @param bool|FALSE $fill
     * @param int        $ln
     * @param string     $x
     * @param string     $y
     * @param bool|TRUE  $reseth
     * @param int        $stretch
     * @param bool|FALSE $ishtml
     * @param bool|TRUE  $autopadding
     * @param int        $maxh
     * @return float|int
     */
//    private function getEstimatedCellHeight($w = 0, $h = 0, $txt, $border = 1, $align = 'L', $fill = FALSE, $ln = 1, $x = '', $y = '', $reseth = TRUE, $stretch = 0, $ishtml = FALSE, $autopadding = TRUE, $maxh = 0)
    private function getEstimatedCellHeight($strContent, $arrConfig)
    {
        $arrConfig['ln'] = 1; // muss 1 sein, sonst wird als Höhe hinterher die gleiche wie vorher geliefert
        //        $pdf = new Report_Pdf(Jelly::factory('Row_Ship'), Jelly::factory('User'));//TCPDF();
        if (!$this->objTmpPdf) {
            $objPdfController = $this->objContainer->get('qipsius.tcpdf');

            $this->objTmpPdf = $pdf = $objPdfController->create();//new Report_Pdf();
        } else {
            $pdf = $this->objTmpPdf;
        }
        $pdf->AddPage();
        // store starting values
        $start_y = $pdf->GetY();
        $start_page = $pdf->getPage();

        $pdf->addCell($strContent, $arrConfig);
//        // call your printing functions with your parameters
        //        $pdf->setFont($this->getFontFamily(), $this->getFontStyle(), $this->getFontSizePt());
        //        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        //        $pdf->MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, $stretch, $ishtml, $autopadding, $maxh);
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // get the new Y
        $end_y = $pdf->GetY();
        $end_page = $pdf->getPage();
        // calculate height
        $height = 0;
        if ($end_page == $start_page) {
            $height = $end_y - $start_y;
        } else {
            for ($page = $start_page; $page <= $end_page; ++$page) {
                $pdf->setPage($page);
                if ($page == $start_page) {
                    // first page
                    $height = $pdf->h - $start_y - $pdf->bMargin;
                } elseif ($page == $end_page) {
                    // last page
                    $height = $end_y - $pdf->tMargin;
                } else {
                    $height = $pdf->h - $pdf->tMargin - $pdf->bMargin;
                }
            }
        }
        // restore previous object
        //        unset($pdf);
        //        $pdf->rollbackTransaction();
        return $height;
    }

/**
 * cleane den Text
 * trim
 * tags raus (bei Reederei zb)
 * \n\r usw raus
 *
 * @param string $strContent
 * @return string
 */
    protected function cleanContent($strContent)
    {
        return trim(strip_tags(preg_replace('/\s+/', ' ', $strContent)));
    }

    private function getConfigValue($strPath, $mixedDefault = false)
    {
        return Arr::path($this->arrConfig, $strPath, $mixedDefault);
    }

}
