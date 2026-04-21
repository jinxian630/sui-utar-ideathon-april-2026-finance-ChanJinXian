<div class="bg-[#12121E] border border-white/5 rounded-2xl p-6 shadow-lg h-full flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h3 class="text-white font-semibold flex items-center gap-2 text-sm">
                Activities 
                <span class="text-[10px] bg-green-500/20 text-green-400 px-2 py-0.5 rounded font-bold">+15%</span>
            </h3>
            <p class="text-xs text-gray-500 mt-1">Show your money flow vs last month</p>
        </div>
        <div class="flex gap-1 bg-[#0D0D14] p-1 rounded-lg border border-white/5" x-data="{ type: 'line' }">
            <button @click="type='line'; window.savingsChart.config.type='line'; window.savingsChart.update();" :class="type == 'line' ? 'text-purple-400 bg-[#12121E] shadow-sm' : 'text-gray-500 hover:text-white'" class="p-1 px-2 text-xs rounded transition uppercase font-semibold">Line</button>
            <button @click="type='bar'; window.savingsChart.config.type='bar'; window.savingsChart.update();" :class="type == 'bar' ? 'text-purple-400 bg-[#12121E] shadow-sm' : 'text-gray-500 hover:text-white'" class="p-1 px-2 text-xs rounded transition uppercase font-semibold">Bar</button>
        </div>
    </div>
    
    <div class="flex-1 w-full min-h-[200px] max-h-[250px] relative">
        <canvas id="savingsChart"></canvas>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('savingsChart').getContext('2d');
            
            let gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(124, 92, 255, 0.4)'); 
            gradient.addColorStop(1, 'rgba(124, 92, 255, 0.0)');
            
            window.savingsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jun 5', 'Jun 12', 'Jun 19', 'Jun 26', 'Jul 5', 'Jul 12', 'Today'],
                    datasets: [{
                        label: 'Total Saved',
                        data: [150, 200, 350, 480, 600, 850, 1025],
                        borderColor: '#7C5CFF',
                        backgroundColor: gradient,
                        borderWidth: 2,
                        pointBackgroundColor: '#12121E',
                        pointBorderColor: '#7C5CFF',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1A1A2E',
                            titleColor: '#8A8A9A',
                            bodyColor: '#FFFFFF',
                            borderColor: 'rgba(255,255,255,0.1)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) { return '+ RM ' + context.parsed.y; }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { color: '#6A6A7A', font: { size: 10 } }
                        },
                        y: {
                            grid: { color: 'rgba(255,255,255,0.03)', drawBorder: false },
                            ticks: { color: '#6A6A7A', font: { size: 10 }, maxTicksLimit: 5 }
                        }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });
        });
    </script>
</div>
