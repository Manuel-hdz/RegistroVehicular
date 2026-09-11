<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Collection;
use ZipStream\CompressionMethod;
use ZipStream\ZipStream;

class WarehouseReportExcelExporter
{
    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array{date_from:string, date_to:string, movement_type:string}  $filters
     */
    public function write(Collection $rows, string $costCenterName, array $filters): void
    {
        $zip = new ZipStream(
            defaultCompressionMethod: CompressionMethod::DEFLATE,
            enableZip64: false,
            defaultEnableZeroHeader: false,
            sendHttpHeaders: false,
        );

        $zip->addFile('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFile('_rels/.rels', $this->rootRelationshipsXml());
        $zip->addFile('docProps/app.xml', $this->appPropertiesXml());
        $zip->addFile('docProps/core.xml', $this->corePropertiesXml());
        $zip->addFile('xl/workbook.xml', $this->workbookXml());
        $zip->addFile('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
        $zip->addFile('xl/styles.xml', $this->stylesXml());
        $zip->addFile('xl/worksheets/sheet1.xml', $this->worksheetXml($rows, $costCenterName, $filters));
        $zip->finish();
    }

    private function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>
XML;
    }

    private function rootRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>
XML;
    }

    private function appPropertiesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Registro Vehicular</Application><DocSecurity>0</DocSecurity><ScaleCrop>false</ScaleCrop><HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Hojas de cálculo</vt:lpstr></vt:variant><vt:variant><vt:i4>1</vt:i4></vt:variant></vt:vector></HeadingPairs><TitlesOfParts><vt:vector size="1" baseType="lpstr"><vt:lpstr>Movimientos</vt:lpstr></vt:vector></TitlesOfParts><Company></Company><LinksUpToDate>false</LinksUpToDate><SharedDoc>false</SharedDoc><HyperlinksChanged>false</HyperlinksChanged><AppVersion>1.0</AppVersion></Properties>
XML;
    }

    private function corePropertiesXml(): string
    {
        $createdAt = now()->utc()->format('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            .'<dc:title>Reporte de almacén</dc:title><dc:creator>Registro Vehicular</dc:creator><cp:lastModifiedBy>Registro Vehicular</cp:lastModifiedBy>'
            .'<dcterms:created xsi:type="dcterms:W3CDTF">'.$createdAt.'</dcterms:created><dcterms:modified xsi:type="dcterms:W3CDTF">'.$createdAt.'</dcterms:modified></cp:coreProperties>';
    }

    private function workbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><bookViews><workbookView xWindow="0" yWindow="0" windowWidth="24000" windowHeight="12000"/></bookViews><sheets><sheet name="Movimientos" sheetId="1" r:id="rId1"/></sheets><calcPr calcId="191029" fullCalcOnLoad="1"/></workbook>
XML;
    }

    private function workbookRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>
XML;
    }

    private function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><numFmts count="2"><numFmt numFmtId="164" formatCode="dd/mm/yyyy hh:mm"/><numFmt numFmtId="165" formatCode="#,##0.00"/></numFmts><fonts count="6"><font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font><font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font><font><i/><sz val="10"/><color rgb="FF4B5563"/><name val="Calibri"/><family val="2"/></font><font><b/><sz val="11"/><color rgb="FF166534"/><name val="Calibri"/><family val="2"/></font><font><b/><sz val="11"/><color rgb="FF92400E"/><name val="Calibri"/><family val="2"/></font></fonts><fills count="6"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF166534"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1D4ED8"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDCFCE7"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFEF3C7"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFD1D5DB"/></left><right style="thin"><color rgb="FFD1D5DB"/></right><top style="thin"><color rgb="FFD1D5DB"/></top><bottom style="thin"><color rgb="FFD1D5DB"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="9"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf><xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center"/></xf><xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment horizontal="center" vertical="top"/></xf><xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment horizontal="right" vertical="top"/></xf><xf numFmtId="0" fontId="4" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="top"/></xf><xf numFmtId="0" fontId="5" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="top"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles><dxfs count="0"/><tableStyles count="0" defaultTableStyle="TableStyleMedium2" defaultPivotStyle="PivotStyleLight16"/></styleSheet>
XML;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array{date_from:string, date_to:string, movement_type:string}  $filters
     */
    private function worksheetXml(Collection $rows, string $costCenterName, array $filters): string
    {
        $movementLabel = match ($filters['movement_type']) {
            'entries' => 'Entradas',
            'exits' => 'Salidas',
            default => 'Entradas y salidas',
        };
        $subtitle = sprintf(
            'Centro de costos: %s | Periodo: %s al %s | Movimientos: %s',
            $costCenterName,
            $filters['date_from'],
            $filters['date_to'],
            $movementLabel
        );
        $lastRow = max(5, $rows->count() + 5);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<dimension ref="A1:P'.$lastRow.'"/>'
            .'<sheetViews><sheetView tabSelected="1" workbookViewId="0"><pane ySplit="5" topLeftCell="A6" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A6" sqref="A6"/></sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="18"/>'
            .'<cols><col min="1" max="1" width="13" customWidth="1"/><col min="2" max="2" width="24" customWidth="1"/><col min="3" max="3" width="19" customWidth="1"/><col min="4" max="5" width="18" customWidth="1"/><col min="6" max="6" width="16" customWidth="1"/><col min="7" max="7" width="30" customWidth="1"/><col min="8" max="8" width="36" customWidth="1"/><col min="9" max="9" width="22" customWidth="1"/><col min="10" max="10" width="13" customWidth="1"/><col min="11" max="16" width="20" customWidth="1"/></cols>'
            .'<sheetData>'
            .'<row r="1" ht="27" customHeight="1">'.$this->inlineCell('A1', 'Reporte de entradas y salidas de almacén', 1).'</row>'
            .'<row r="2" ht="22" customHeight="1">'.$this->inlineCell('A2', $subtitle, 2).'</row>'
            .'<row r="3" ht="20" customHeight="1">'.$this->inlineCell('A3', 'Generado: '.now()->format('d/m/Y H:i'), 2).'</row>'
            .'<row r="4" ht="8" customHeight="1"></row>'
            .'<row r="5" ht="34" customHeight="1">';

        foreach ($this->columns() as $index => $label) {
            $xml .= $this->inlineCell($this->columnLetter($index + 1).'5', $label, 3);
        }

        $xml .= '</row>';

        foreach ($rows->values() as $index => $row) {
            $rowNumber = $index + 6;
            $movementStyle = $row['movement'] === 'Entrada' ? 7 : 8;
            $xml .= '<row r="'.$rowNumber.'">'
                .$this->inlineCell('A'.$rowNumber, $row['movement'], $movementStyle)
                .$this->inlineCell('B'.$rowNumber, $row['cost_center'])
                .$this->dateCell('C'.$rowNumber, $row['date'])
                .$this->inlineCell('D'.$rowNumber, $row['folio'])
                .$this->inlineCell('E'.$rowNumber, $row['type'])
                .$this->inlineCell('F'.$rowNumber, $row['key'])
                .$this->inlineCell('G'.$rowNumber, $row['material'])
                .$this->inlineCell('H'.$rowNumber, $row['characteristics'])
                .$this->inlineCell('I'.$rowNumber, $row['location'])
                .$this->numberCell('J'.$rowNumber, $row['quantity'])
                .$this->inlineCell('K'.$rowNumber, $row['dispatched_by'])
                .$this->inlineCell('L'.$rowNumber, $row['carried_by'])
                .$this->inlineCell('M'.$rowNumber, $row['destination'])
                .$this->inlineCell('N'.$rowNumber, $row['responsible'])
                .$this->inlineCell('O'.$rowNumber, $row['registered_by'])
                .$this->inlineCell('P'.$rowNumber, $row['status'])
                .'</row>';
        }

        return $xml.'</sheetData><autoFilter ref="A5:P'.$lastRow.'"/><mergeCells count="3"><mergeCell ref="A1:P1"/><mergeCell ref="A2:P2"/><mergeCell ref="A3:P3"/></mergeCells><printOptions horizontalCentered="1"/><pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/><pageSetup orientation="landscape" fitToWidth="1" fitToHeight="0" paperSize="9"/></worksheet>';
    }

    /** @return array<int, string> */
    private function columns(): array
    {
        return [
            'Movimiento', 'Centro de costos', 'Fecha', 'Folio', 'Tipo', 'Clave', 'Material',
            'Características', 'Ubicación', 'Cantidad', 'Despachó', 'Llevó', 'Destino',
            'Responsable', 'Registró', 'Estatus',
        ];
    }

    private function inlineCell(string $reference, mixed $value, int $style = 4): string
    {
        return '<c r="'.$reference.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'
            .$this->escape($value)
            .'</t></is></c>';
    }

    private function dateCell(string $reference, mixed $value): string
    {
        if (! $value instanceof DateTimeInterface) {
            return $this->inlineCell($reference, '');
        }

        $localDate = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i:s',
            $value->format('Y-m-d H:i:s'),
            new DateTimeZone('UTC')
        );
        $serial = $localDate ? ($localDate->getTimestamp() / 86400) + 25569 : 0;

        return '<c r="'.$reference.'" s="5"><v>'.rtrim(rtrim(sprintf('%.10F', $serial), '0'), '.').'</v></c>';
    }

    private function numberCell(string $reference, mixed $value): string
    {
        $number = is_numeric($value) ? (float) $value : 0;

        return '<c r="'.$reference.'" s="6"><v>'.rtrim(rtrim(sprintf('%.4F', $number), '0'), '.').'</v></c>';
    }

    private function columnLetter(int $number): string
    {
        return chr(64 + $number);
    }

    private function escape(mixed $value): string
    {
        $value = (string) ($value ?? '');
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
