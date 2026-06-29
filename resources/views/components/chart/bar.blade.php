{{-- resources/views/components/chart/bar.blade.php --}}
@props(['id', 'title', 'labels', 'data', 'colors' => null, 'height' => '72'])

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
    const colors = @json($colors ?? []);
    
    const chartColors = colors.length > 0 ? colors : defaultColors.slice(0, data.length);
    
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'IKM (%)',
                data: data,
                backgroundColor: chartColors.map(color => color + '80'),
                borderColor: chartColors,
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + '%';
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    max: 100, 
                    ticks: { callback: function(v) { return v + '%'; } } 
                }
            }
        }
    });
});
</script>
@endpush