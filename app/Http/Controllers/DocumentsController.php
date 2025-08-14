<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DocumentsController extends Controller
{
    public function fetchDocumentTemplates(Request $request)
    {
        if (isset($request->title) && $request->title != '') {

            $document_templates = DocumentTemplate::where('title', 'LIKE', "%$request->title%")->orderBy('title')->get()->groupBy('first_letter');
        } else {

            $document_templates = DocumentTemplate::orderBy('title')->get()->groupBy('first_letter');
        }
        // foreach ($document_templates as $template) {
        //     $template->first_letter = substr($template->title, 0, 1);
        //     $template->save();
        // }
        return response()->json(compact('document_templates'), 200);
    }

    public function uploadDocumentTemplate(Request $request)
    {
        $title = ucwords($request->title);
        $template = DocumentTemplate::where('title', $title)->first();
        if (!$template) {
            $template = new DocumentTemplate();

            $template->title = $title;
            $template->first_letter = substr($title, 0, 1);
            $template->applicable_modules = isset($request->applicable_modules) ? json_encode($request->applicable_modules) : null;

            if ($request->file('file_uploaded') != null && $request->file('file_uploaded')->isValid()) {
                $file_name = str_replace(' ', '_', strtolower($title)) . '_template' . "." . $request->file('file_uploaded')->guessClientExtension();
                $link = $request->file('file_uploaded')->storeAs('document_template', $file_name, 'public');
                $template->link = $link;
            }
            if (isset($request->external_link) && $request->external_link != '') {
                $template->external_link = $request->external_link;
            }
            $template->save();
        }
    }
    public function updateDocumentTemplate(Request $request)
    {
        $id = $request->id;
        $title = $request->title;
        $template = DocumentTemplate::find($id);

        $template->title = $title;
        $template->first_letter = substr($title, 0, 1);

        if ($request->file('file_uploaded') != null && $request->file('file_uploaded')->isValid()) {
            $file_name = str_replace(' ', '_', strtolower($title)) . '_template' . "." . $request->file('file_uploaded')->guessClientExtension();
            $link = $request->file('file_uploaded')->storeAs('document_template', $file_name, 'public');
            $template->link = $link;
        }
        if (isset($request->external_link) && $request->external_link != '') {
            $template->external_link = $request->external_link;
        }
        $template->save();

    }

    public function destroy(Request $request, DocumentTemplate $document)
    {
        if ($document->link != null) {
            \Storage::disk('public')->delete($document->link);
        }
        $document->delete();
        return response()->json([], 204);
    }


    //
    // public function formatDocToSFDTOlderImplementation(Request $request)
    // {
    //     $id = $request->id;
    //     $table = $request->table;
    //     $document_table = DB::table($table)->find($id);

    //     // if we have an already converted file, we send that instead
    //     if ($document_table->sfdt_format != NULL) {
    //         // return response()->json(['sfdt' => $document_table->sfdt_format], 200);
    //     }
    //     // file not converted to sfdt, let's convert it
    //     define('MULTIPART_BOUNDARY', '--------------------------' . microtime(true));
    //     $header = 'Content-Type: multipart/form-data; boundary=' . MULTIPART_BOUNDARY;
    //     // equivalent to <input type="file" name="files"/>
    //     define('FORM_FIELD', 'files');

    //     $filename = portalPulicPath($document_table->link);
    //     $file_contents = file_get_contents($filename);

    //     $content =  "--" . MULTIPART_BOUNDARY . "\r\n" .
    //         "Content-Disposition: form-data; name=\"" . FORM_FIELD . "\"; filename=\"" . basename($filename) . "\"\r\n" .
    //         "Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document\r\n\r\n" .
    //         $file_contents . "\r\n";

    //     // add some POST fields to the request too: $_POST['foo'] = 'bar'
    //     $content .= "--" . MULTIPART_BOUNDARY . "\r\n" .
    //         "Content-Disposition: form-data; name=\"foo\"\r\n\r\n" .
    //         "bar\r\n";
    //     // signal end of request (note the trailing "--")
    //     $content .= "--" . MULTIPART_BOUNDARY . "--\r\n";

    //     $context = stream_context_create(array(
    //         'http' => array(
    //             'method' => 'POST',
    //             'header' => $header,
    //             'content' => $content,
    //         )
    //     ));
    //     $to_url = 'https://ej2services.syncfusion.com/production/web-services/api/documenteditor/Import';

    //     return file_get_contents($to_url, false, $context);
    // }
    public function formatDocToSFDT(Request $request)
    {
        $document_path = $request->path;
        // file not converted to sfdt, let's convert it
        define('MULTIPART_BOUNDARY', '--------------------------' . microtime(true));
        $header = 'Content-Type: multipart/form-data; boundary=' . MULTIPART_BOUNDARY;
        // equivalent to <input type="file" name="files"/>
        define('FORM_FIELD', 'files');

        $filename = portalPulicPath($document_path);
        try {
            $file_contents = file_get_contents($filename);
            // $base64 = base64_encode($file_contents);
            // return response()->json(['sfdt' => $base64], 200);
            $content = "--" . MULTIPART_BOUNDARY . "\r\n" .
                "Content-Disposition: form-data; name=\"" . FORM_FIELD . "\"; filename=\"" . basename($filename) . "\"\r\n" .
                "Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document\r\n\r\n" .
                $file_contents . "\r\n";

            // add some POST fields to the request too: $_POST['foo'] = 'bar'
            $content .= "--" . MULTIPART_BOUNDARY . "\r\n" .
                "Content-Disposition: form-data; name=\"foo\"\r\n\r\n" .
                "bar\r\n";
            // signal end of request (note the trailing "--")
            $content .= "--" . MULTIPART_BOUNDARY . "--\r\n";

            $context = stream_context_create(
                array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => $header,
                        'content' => $content,
                    )
                )
            );
            $to_url = 'https://ej2services.syncfusion.com/production/web-services/api/documenteditor/Import';

            return file_get_contents($to_url, false, $context);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'An error occurred while processing the document. Please try again.'], 500);
        }

    }
    public function fetchExcelDocument(Request $request)
    {
        $document_path = $request->path;
        // file not converted to sfdt, let's convert it
        define('MULTIPART_BOUNDARY', '--------------------------' . microtime(true));
        $header = 'Content-Type: multipart/form-data; boundary=' . MULTIPART_BOUNDARY;
        // // equivalent to <input type="file" name="files"/>
        define('FORM_FIELD', 'files');

        $filename = portalPulicPath($document_path);
        //try {
        $file_contents = file_get_contents($filename);
        return base64_encode($file_contents);

    }
    public function fetchJsonFormattedExcelDocument(Request $request)
    {
        $document_path = $request->path;
        // file not converted to sfdt, let's convert it
        define('MULTIPART_BOUNDARY', '--------------------------' . microtime(true));
        $header = 'Content-Type: multipart/form-data; boundary=' . MULTIPART_BOUNDARY;
        // // equivalent to <input type="file" name="files"/>
        define('FORM_FIELD', 'files');

        $filename = portalPulicPath($document_path);
        //try {
        return $file_contents = file_get_contents($filename);

    }


    public function saveDocTemplate(Request $request)
    {
        $document_path = $request->path;
        if ($request->file('file_to_be_saved') != null && $request->file('file_to_be_saved')->isValid()) {

            $temp_path = $request->file('file_to_be_saved')->getRealPath();
            $file = file_get_contents($temp_path);
            $base64 = base64_encode($file);
            $path = portalPulicPath($document_path);
            file_put_contents($path, base64_decode($base64));

            return response()->json(['message' => 'Saved Successfully'], 200);
        }
        return response()->json(['message' => 'An error occured. Please try again'], 500);
    }

    public function saveClientCopy(Request $request)
    {
        $client = $this->getClient();
        $document_path = $request->path;
        $folder_key = str_replace(' ', '_', ucwords($client->name));
        if ($request->file('file_to_be_saved') != null && $request->file('file_to_be_saved')->isValid()) {
            $upload = Upload::find($request->id);
            // return $request;
            $formated_name = str_replace(' ', '_', ucwords($request->title));
            $file_name = 'evidence_for_' . $formated_name . '_template_' . $upload->template_id . "." . $request->file('file_to_be_saved')->guessClientExtension();
            $link = $request->file('file_to_be_saved')->storeAs('clients/' . $folder_key . '/document', $file_name, 'public');
            $upload->link = $link;
            $upload->last_modified_by = $this->getUser()->id;
            $upload->save();

            return response()->json(['message' => 'Saved Successfully'], 200);
        }
        return response()->json(['message' => 'An error occured. Please try again'], 500);
    }

    public function exportExcel(Request $request)
    {
        // Syncfusion sends the spreadsheet data in "json" field
        $data = json_decode($request->JSONData, 1);
        // return $data['sheets'];
        if (!isset($data['sheets']) || !is_array($data['sheets'])) {
            return response()->json(['error' => 'Invalid spreadsheet data'], 400);
        }

        // Create new PhpSpreadsheet instance
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // remove default sheet

        foreach ($data['sheets'] as $sheetIndex => $sheetData) {
            $sheetTitle = $sheetData['name'] ?? 'Sheet' . ($sheetIndex + 1);
            $worksheet = $spreadsheet->createSheet($sheetIndex);
            $worksheet->setTitle($sheetTitle);

            if (isset($sheetData['rows']) && is_array($sheetData['rows'])) {
                foreach ($sheetData['rows'] as $rowIndex => $row) {
                    if (!isset($row['cells']))
                        continue;

                    foreach ($row['cells'] as $cellIndex => $cell) {
                        $value = $cell['value'] ?? '';
                        $col = $this->columnLetter($cellIndex + 1); // 1-based
                        $worksheet->setCellValue($col . ($rowIndex + 1), $value);
                    }
                }
            }
        }

        // Remove default empty sheet if extra
        if ($spreadsheet->getSheetCount() > count($data['sheets'])) {
            $spreadsheet->removeSheetByIndex(count($data['sheets']));
        }

        // Save file
        $fileName = 'spreadsheet_' . time() . '.xlsx';
        $filePath = storage_path('app/spreadsheets/' . $fileName);

        // Ensure directory exists
        if (!is_dir(storage_path('app/spreadsheets'))) {
            mkdir(storage_path('app/spreadsheets'), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->json([
            'status' => 'success',
            'file' => $fileName,
            'path' => $filePath
        ]);
    }

    private function columnLetter($c)
    {
        $letter = '';
        while ($c > 0) {
            $p = ($c - 1) % 26;
            $c = intval(($c - $p) / 26);
            $letter = chr(65 + $p) . $letter;
        }
        return $letter;
    }
}
