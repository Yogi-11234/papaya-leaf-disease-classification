<?php

namespace App\Http\Controllers;

use App\Models\ModelMetricsFold;
use App\Models\DatasetDistribusi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Get metrics per fold
        $metrics = ModelMetricsFold::orderBy('fold', 'asc')->get();

        // 2. Compute averages & std deviations
        $summary = [
            'accuracy_mean' => 0.0, 'accuracy_std' => 0.0,
            'precision_mean' => 0.0, 'precision_std' => 0.0,
            'recall_mean' => 0.0, 'recall_std' => 0.0,
            'f1_mean' => 0.0, 'f1_std' => 0.0,
            'best_fold' => null, 'best_accuracy' => 0.0,
            'worst_fold' => null, 'worst_accuracy' => 100.0,
        ];

        if ($metrics->count() > 0) {
            $accuracies = $metrics->pluck('accuracy')->toArray();
            $precisions = $metrics->pluck('precision')->toArray();
            $recalls = $metrics->pluck('recall')->toArray();
            $f1s = $metrics->pluck('f1')->toArray();

            $summary['accuracy_mean'] = array_sum($accuracies) / count($accuracies);
            $summary['precision_mean'] = array_sum($precisions) / count($precisions);
            $summary['recall_mean'] = array_sum($recalls) / count($recalls);
            $summary['f1_mean'] = array_sum($f1s) / count($f1s);

            // Calculate std dev
            $summary['accuracy_std'] = $this->calculateStdDev($accuracies);
            $summary['precision_std'] = $this->calculateStdDev($precisions);
            $summary['recall_std'] = $this->calculateStdDev($recalls);
            $summary['f1_std'] = $this->calculateStdDev($f1s);

            // Find best and worst fold
            foreach ($metrics as $metric) {
                $accPercent = $metric->accuracy;
                if ($accPercent > $summary['best_accuracy']) {
                    $summary['best_accuracy'] = $accPercent;
                    $summary['best_fold'] = $metric->fold;
                }
                if ($accPercent < $summary['worst_accuracy']) {
                    $summary['worst_accuracy'] = $accPercent;
                    $summary['worst_fold'] = $metric->fold;
                }
            }
        }

        // 3. Get dataset class distribution
        $distribution = DatasetDistribusi::with('penyakit')->get();
        $totalImages = $distribution->sum('jumlah_citra');

        return view('dashboard', compact('metrics', 'summary', 'distribution', 'totalImages'));
    }

    /**
     * Standard deviation helper
     */
    private function calculateStdDev(array $values): float
    {
        $num_elements = count($values);
        if ($num_elements <= 1) {
            return 0.0;
        }
        $mean = array_sum($values) / $num_elements;
        $variance = 0.0;
        foreach ($values as $val) {
            $variance += pow(($val - $mean), 2);
        }
        $average_variance = $variance / ($num_elements - 1);
        return sqrt($average_variance);
    }
}
