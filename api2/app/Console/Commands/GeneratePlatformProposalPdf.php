<?php

namespace App\Console\Commands;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GeneratePlatformProposalPdf extends Command
{
    protected $signature = 'docs:platform-proposal
                            {--output= : مسار ملف PDF الناتج}
                            {--copy-downloads : نسخ إلى مجلد Downloads}';

    protected $description = 'Generate commercial proposal PDF for the SME platform';

    public function handle(): int
    {
        $generatedAt = now()->format('Y-m-d');
        $version = '1.0';

        $pdf = Pdf::loadView('docs.platform-commercial-proposal', [
            'generatedAt' => $generatedAt,
            'version'     => $version,
        ])->setPaper('a4', 'portrait');

        $defaultPath = base_path('docs/Platform-Commercial-Proposal-AR.pdf');
        $output = $this->option('output') ?: $defaultPath;

        File::ensureDirectoryExists(dirname($output));
        File::put($output, $pdf->output());

        $this->info("PDF generated: {$output}");

        if ($this->option('copy-downloads')) {
            $downloads = rtrim(getenv('USERPROFILE') ?: getenv('HOME') ?: '', '\\/')
                . DIRECTORY_SEPARATOR . 'Downloads'
                . DIRECTORY_SEPARATOR . 'Platform-Commercial-Proposal-AR.pdf';

            if (is_dir(dirname($downloads))) {
                File::put($downloads, $pdf->output());
                $this->info("Copied to: {$downloads}");
            }
        }

        return self::SUCCESS;
    }
}
