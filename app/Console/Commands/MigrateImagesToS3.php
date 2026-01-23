<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\Review;
use App\Models\Tribute;

class MigrateImagesToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:images-to-s3 
                            {--dry-run : Run without actually uploading files}
                            {--skip-photos : Skip migrating photos}
                            {--skip-profiles : Skip migrating profile/cover pictures}
                            {--skip-reviews : Skip migrating reviews}
                            {--skip-tributes : Skip migrating tributes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing local images to AWS S3';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No files will be uploaded');
        }

        // Check if S3 is configured
        if (config('filesystems.default') !== 's3') {
            $this->error('❌ S3 is not configured as default filesystem.');
            $this->info('Set FILESYSTEM_DISK=s3 in your .env file');
            return 1;
        }

        $this->info('🚀 Starting image migration to S3...');
        $this->newLine();

        $totalMigrated = 0;
        $totalFailed = 0;
        $totalSkipped = 0;

        // Migrate Photos
        if (!$this->option('skip-photos')) {
            $this->info('📸 Migrating photos...');
            $result = $this->migratePhotos($dryRun);
            $totalMigrated += $result['migrated'];
            $totalFailed += $result['failed'];
            $totalSkipped += $result['skipped'];
            $this->newLine();
        }

        // Migrate Profile Pictures
        if (!$this->option('skip-profiles')) {
            $this->info('👤 Migrating profile pictures...');
            $result = $this->migrateProfilePictures($dryRun);
            $totalMigrated += $result['migrated'];
            $totalFailed += $result['failed'];
            $totalSkipped += $result['skipped'];
            $this->newLine();
        }

        // Migrate Reviews
        if (!$this->option('skip-reviews')) {
            $this->info('⭐ Migrating review images...');
            $result = $this->migrateReviews($dryRun);
            $totalMigrated += $result['migrated'];
            $totalFailed += $result['failed'];
            $totalSkipped += $result['skipped'];
            $this->newLine();
        }

        // Migrate Tributes
        if (!$this->option('skip-tributes')) {
            $this->info('💝 Migrating tribute images...');
            $result = $this->migrateTributes($dryRun);
            $totalMigrated += $result['migrated'];
            $totalFailed += $result['failed'];
            $totalSkipped += $result['skipped'];
            $this->newLine();
        }

        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 Migration Summary:');
        $this->info("   ✅ Migrated: {$totalMigrated}");
        $this->info("   ❌ Failed: {$totalFailed}");
        $this->info("   ⏭️  Skipped: {$totalSkipped}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if ($dryRun) {
            $this->warn('⚠️  This was a dry run. Run without --dry-run to actually migrate files.');
        }

        return 0;
    }

    private function migratePhotos($dryRun)
    {
        $migrated = 0;
        $failed = 0;
        $skipped = 0;

        $photos = Photo::whereNotNull('image')
            ->where('image', '!=', 'youtube_placeholder')
            ->get();

        $bar = $this->output->createProgressBar($photos->count());
        $bar->start();

        foreach ($photos as $photo) {
            $localPath = public_path('images/profile/photos/' . $photo->image);
            $s3Path = 'images/profile/photos/' . $photo->image;

            if (!file_exists($localPath)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                if (!$dryRun) {
                    Storage::disk('s3')->put($s3Path, file_get_contents($localPath));
                }
                $migrated++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("Failed to migrate {$photo->image}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return ['migrated' => $migrated, 'failed' => $failed, 'skipped' => $skipped];
    }

    private function migrateProfilePictures($dryRun)
    {
        $migrated = 0;
        $failed = 0;
        $skipped = 0;

        $profiles = Profile::whereNotNull('profile_picture')
            ->orWhereNotNull('cover_picture')
            ->get();

        $bar = $this->output->createProgressBar($profiles->count() * 2);
        $bar->start();

        foreach ($profiles as $profile) {
            // Profile picture
            if ($profile->profile_picture) {
                $localPath = public_path('images/profile/profile_pictures/' . $profile->profile_picture);
                $s3Path = 'images/profile/profile_pictures/' . $profile->profile_picture;

                if (file_exists($localPath)) {
                    try {
                        if (!$dryRun) {
                            Storage::disk('s3')->put($s3Path, file_get_contents($localPath));
                        }
                        $migrated++;
                    } catch (\Exception $e) {
                        $failed++;
                        $this->newLine();
                        $this->error("Failed to migrate profile picture {$profile->profile_picture}: " . $e->getMessage());
                    }
                } else {
                    $skipped++;
                }
            } else {
                $skipped++;
            }
            $bar->advance();

            // Cover picture
            if ($profile->cover_picture) {
                $localPath = public_path('images/profile/cover_pictures/' . $profile->cover_picture);
                $s3Path = 'images/profile/cover_pictures/' . $profile->cover_picture;

                if (file_exists($localPath)) {
                    try {
                        if (!$dryRun) {
                            Storage::disk('s3')->put($s3Path, file_get_contents($localPath));
                        }
                        $migrated++;
                    } catch (\Exception $e) {
                        $failed++;
                        $this->newLine();
                        $this->error("Failed to migrate cover picture {$profile->cover_picture}: " . $e->getMessage());
                    }
                } else {
                    $skipped++;
                }
            } else {
                $skipped++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return ['migrated' => $migrated, 'failed' => $failed, 'skipped' => $skipped];
    }

    private function migrateReviews($dryRun)
    {
        $migrated = 0;
        $failed = 0;
        $skipped = 0;

        $reviews = Review::whereNotNull('image')->get();

        $bar = $this->output->createProgressBar($reviews->count());
        $bar->start();

        foreach ($reviews as $review) {
            $localPath = public_path('images/reviews/' . $review->image);
            $s3Path = 'images/reviews/' . $review->image;

            if (!file_exists($localPath)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                if (!$dryRun) {
                    Storage::disk('s3')->put($s3Path, file_get_contents($localPath));
                }
                $migrated++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("Failed to migrate review image {$review->image}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return ['migrated' => $migrated, 'failed' => $failed, 'skipped' => $skipped];
    }

    private function migrateTributes($dryRun)
    {
        $migrated = 0;
        $failed = 0;
        $skipped = 0;

        $tributes = Tribute::whereNotNull('image')->get();

        $bar = $this->output->createProgressBar($tributes->count());
        $bar->start();

        foreach ($tributes as $tribute) {
            $localPath = public_path('images/profile/tributes/' . $tribute->image);
            $s3Path = 'images/profile/tributes/' . $tribute->image;

            if (!file_exists($localPath)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                if (!$dryRun) {
                    Storage::disk('s3')->put($s3Path, file_get_contents($localPath));
                }
                $migrated++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("Failed to migrate tribute image {$tribute->image}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return ['migrated' => $migrated, 'failed' => $failed, 'skipped' => $skipped];
    }
}
