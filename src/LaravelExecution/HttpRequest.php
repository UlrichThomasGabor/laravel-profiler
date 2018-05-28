<?php

namespace JKocik\Laravel\Profiler\LaravelExecution;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use JKocik\Laravel\Profiler\Contracts\ExecutionRequest;

class HttpRequest implements ExecutionRequest
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * HttpRequest constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Collection
     */
    public function meta(): Collection
    {
        return Collection::make([
            'type' => 'http',
            'method' => $this->request->method(),
            'path' => $this->request->path(),
            'ajax' => $this->request->ajax(),
            'json' => $this->request->isJson(),
        ]);
    }

    /**
     * @return Collection
     */
    public function data(): Collection
    {
        return Collection::make([
            'pjax' => $this->request->pjax(),
            'url' => $this->request->url(),
            'query' => $this->request->query(),
            'ip' => $this->request->ip(),
            'server' => $this->request->server(),
            'header' => $this->request->header(),
            'input' => $this->request->input(),
            'files' => $this->files(),
            'cookie' => $this->request->cookie(),
        ]);
    }

    /**
     * @return Collection
     */
    protected function files(): Collection
    {
        return Collection::make($this->request->allFiles())->map(function (UploadedFile $file) {
            return [
                'client_original_name' => $file->getClientOriginalName(),
                'client_original_extension' => $file->getClientOriginalExtension(),
                'client_mime_type' => $file->getClientMimeType(),
                'client_size' => $file->getClientSize(),
                'path' => $file->path(),
            ];
        });
    }
}