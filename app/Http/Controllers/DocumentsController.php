<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentsController extends Controller
{
    public function fetchDocumentTemplates(Request $request)
    {
        $document_templates = DocumentTemplate::orderBy('title')->get()->groupBy('first_letter');
        // foreach ($document_templates as $template) {
        //     $template->first_letter = substr($template->title, 0, 1);
        //     $template->save();
        // }
        return response()->json(compact('document_templates'), 200);
    }

    public function uploadDocumentTemplate(Request $request)
    {
        $title = $request->title;
        $template = DocumentTemplate::where('title', $title)->first();
        if (!$template) {
            $template = new DocumentTemplate();

            if ($request->file('file_uploaded') != null && $request->file('file_uploaded')->isValid()) {
                $file_name = 'template_for_' . str_replace(' ', '-', strtolower($title)) . "." . $request->file('file_uploaded')->guessClientExtension();
                $link = $request->file('file_uploaded')->storeAs('document_template', $file_name, 'public');
                $template->title = $title;
                $template->link = $link;
                $template->first_letter = substr($title, 0, 1);
                $template->save();
            }
        }
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
    }
    public function saveBlobToDoc(Request $request)
    {
        $document_path = $request->path;
        if ($request->file('file_to_be_saved') != null && $request->file('file_to_be_saved')->isValid()) {
            // return $request;

            $temp_path = $request->file('file_to_be_saved')->getRealPath();
            $file = file_get_contents($temp_path);
            $base64 = base64_encode($file);
            $path = portalPulicPath($document_path);
            file_put_contents($path, base64_decode($base64));

            return response()->json(['message' => 'Saved Successfully'], 200);
        }
        return response()->json(['message' => 'An error occured. Please try again'], 500);
    }
}
