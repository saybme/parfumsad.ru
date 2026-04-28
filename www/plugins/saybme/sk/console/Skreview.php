<?php namespace Saybme\Sk\Console;

use Illuminate\Console\Command;
use Saybme\Sk\Classes\Catalog\ReviewClass;

/**
 * Skreview Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class Skreview extends Command
{
    /**
     * @var string signature for the console command.
     */
    protected $signature = 'sk:skreview';

    /**
     * @var string description is the console command description
     */
    protected $description = 'No description provided yet...';

    /**
     * handle executes the console command.
     */
    public function handle()
    {
        $review = ReviewClass::createRandomReview();

        $result = json_encode([
            'message' => 'Отзыв добавлен',
            'review' => $review->toArray()
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $this->output->writeln($result);
    }



}
