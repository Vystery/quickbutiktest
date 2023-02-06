<?php

namespace App\Console\Commands;

use App\Http\API\QuickButik\QuickButikClient;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class Quickbutik extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quickbutik test assignment';

    private ProgressBar $bar;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Getting all the orders');
        $this->createAndStartProgressBar(1);

        $client = new QuickButikClient();
        $orders = $client->getOrders();
        $totalOrders = count($orders);
        
        $this->finishBar();
        
        $this->info('Summing up the total order amount');
        $this->createAndStartProgressBar($totalOrders);

        $totalAmount = array_map(function($order) {
            $this->advanceBar();
            return $order['total_amount'];
        }, $orders);

        $avg = round(array_sum($totalAmount) / $totalOrders, 2);
        $this->finishBar();

        $this->info('Getting all the products within orders');
        $this->createAndStartProgressBar($totalOrders);

        $popularProducts = $this->popularProductsByTotalInOrders($orders, $client);
        $this->finishBar();
        
        $productList = [];
        foreach($popularProducts as $index => $products) {
            foreach($products as $productId) {
                if (!isset($productList[$productId])) {
                    $productList[$productId] = 1;
                } else {
                    $productList[$productId]++;
                }
            }
        }
        arsort($productList);
        $top3 = '';
        $count = 1;
        foreach($productList as $productId => $total) {
            $top3 .= $productId . '[' . $total . ']';
            $count++;
            
            if ($count > 3) {
                break;
            } else {
                $top3 .= ', ';
            }
        }
        
        $this->table([
            'Avg amount',
            'Top 3 product ids and their totals - productId[total]',
        ], [0 => [$avg, $top3]]);

        return Command::SUCCESS;
    }

    private function popularProductsByTotalInOrders(array $orders, QuickButikClient $client): array
    {
        return array_map(function($order) use ($client) {
            $this->advanceBar();
            return array_map(function($product) {
                return $product['product_id'];
            }, $client->getOrder($order['order_id'])['products']);
        }, $orders);
    }

    private function createAndStartProgressBar(int $count): void
    {
        $this->bar = $this->output->createProgressBar($count);
        $this->bar->start();
    }

    private function advanceBar(): void
    {
        $this->bar->advance();
    }

    private function finishBar(): void
    {
        $this->bar->finish();
        $this->newLine();
    }
}
