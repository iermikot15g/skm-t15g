{{-- resources/views/components/chart/pie.blade.php --}}
@props(['id', 'title', 'labels', 'data', 'type' => 'doughnut', 'height' => '72'])

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
    
    const data = @json($data);
    const labels = @json($labels);
    
    if (!data || data.length === 0 || !labels || labels.length === 0) return;
    
    const defaultColors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'];
    
    new Chart(canvas, {
        type: '{{ $type }}',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: defaultColors.slice(0, data.length),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 10 } } }
            }
        }
    });
});
</script>
@endpush