{{-- resources/views/components/chart/radar.blade.php --}}
@props(['id', 'title', 'labels', 'datasets', 'height' => '72'])

<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ $title }}</h3>
    <div class="h-{{ $height }}">
        <canvas id="{{ $id }}"></canvas>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('{{ $id }}');
    if (!canvas) return;
    
    const datasets = @json($datasets);
    const labels = @json($labels);
    
    if (!datasets || datasets.length === 0 || !labels || labels.length === 0) return;
    
    const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
    
    const chartDatasets = datasets.map((dataset, index) => ({
        label: dataset.label,
        data: dataset.data.map(d => d.nilai),
        borderColor: colors[index % colors.length],
        backgroundColor: colors[index % colors.length] + '20',
        borderWidth: 2,
        pointRadius: 3,
    }));
    
    new Chart(canvas, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: chartDatasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 10 } } }
            },
            scales: {
                r: { min: 1, max: 4, ticks: { stepSize: 0.5 } }
            }
        }
    });
});
</script>
@endpush