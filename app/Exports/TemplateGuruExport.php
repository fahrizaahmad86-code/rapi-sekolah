<?php

namespace App\Exports;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class TemplateGuruExport
{
    public function downloadPath(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'template-guru-');
        if ($path === false) {
            throw new \RuntimeException('Gagal menyiapkan file template Excel.');
        }

        $writer = new Writer();
        try {
            $writer->openToFile($path);
            $writer->addRow(Row::fromValues(['nama_lengkap', 'username', 'email', 'password']));
            $writer->close();
        } catch (\Throwable $exception) {
            if (is_file($path)) {
                unlink($path);
            }

            throw $exception;
        }

        return $path;
    }
}
