<!-- EMI Calculator Floating Button -->
<button id="emiTriggerBtn" class="fixed right-6 bottom-6 w-14 h-14 bg-gradient-to-br from-secondary to-yellow-600 rounded-full shadow-2xl hover:shadow-secondary/50 transition-all duration-300 z-40 flex items-center justify-center text-white hover:scale-110 group">
    <i class="fa-solid fa-calculator text-xl group-hover:rotate-12 transition-transform"></i>
</button>

<!-- EMI Calculator Sidebar -->
<div id="emiSidebar" class="fixed right-0 top-0 bottom-0 w-full md:w-[420px] bg-white shadow-2xl transform translate-x-full transition-all duration-500 ease-in-out z-50 overflow-y-auto">
    <!-- Header -->
    <div class="sticky top-0 bg-gradient-to-br from-secondary to-yellow-600 p-6 text-white z-10">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <i class="fa-solid fa-calculator text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold font-display">EMI Calculator</h3>
                    <p class="text-xs text-white/80">Plan Your Home Loan</p>
                </div>
            </div>
            <button id="closeSidebarBtn" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 transition flex items-center justify-center">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Calculator Content -->
    <div class="p-6 space-y-6">
        <!-- Loan Amount -->
        <div>
            <label class="flex items-center justify-between mb-3">
                <span class="text-sm font-bold text-slate-700 uppercase tracking-wide">Loan Amount</span>
                <span class="text-lg font-black text-secondary">₹<span id="widgetLoanAmountDisplay">50,00,000</span></span>
            </label>
            <input type="range" id="widgetLoanAmount" min="100000" max="50000000" step="100000" value="5000000" 
                   class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer widget-slider">
            <div class="flex justify-between text-xs text-slate-400 mt-2">
                <span>₹1L</span>
                <span>₹5Cr</span>
            </div>
        </div>

        <!-- Interest Rate -->
        <div>
            <label class="flex items-center justify-between mb-3">
                <span class="text-sm font-bold text-slate-700 uppercase tracking-wide">Interest Rate (p.a.)</span>
                <span class="text-lg font-black text-secondary"><span id="widgetInterestRateDisplay">8.5</span>%</span>
            </label>
            <input type="range" id="widgetInterestRate" min="5" max="20" step="0.1" value="8.5" 
                   class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer widget-slider">
            <div class="flex justify-between text-xs text-slate-400 mt-2">
                <span>5%</span>
                <span>20%</span>
            </div>
        </div>

        <!-- Loan Tenure -->
        <div>
            <label class="flex items-center justify-between mb-3">
                <span class="text-sm font-bold text-slate-700 uppercase tracking-wide">Loan Tenure</span>
                <span class="text-lg font-black text-secondary"><span id="widgetTenureDisplay">20</span> Years</span>
            </label>
            <input type="range" id="widgetTenure" min="1" max="30" step="1" value="20" 
                   class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer widget-slider">
            <div class="flex justify-between text-xs text-slate-400 mt-2">
                <span>1 Year</span>
                <span>30 Years</span>
            </div>
        </div>

        <!-- EMI Result Card -->
        <div class="bg-gradient-to-br from-secondary to-yellow-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <i class="fa-solid fa-indian-rupee-sign text-lg"></i>
                </div>
                <div class="flex-1">
                    <p class="text-white/80 text-xs font-medium mb-1">Monthly EMI</p>
                    <h3 class="text-3xl font-black font-display" id="widgetEmiAmount">₹41,822</h3>
                </div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 text-xs space-y-1">
                <div class="flex justify-between items-center">
                    <span class="text-white/70">Principal Amount</span>
                    <span class="font-bold" id="widgetPrincipalAmount">₹50,00,000</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-white/70">Total Interest</span>
                    <span class="font-bold" id="widgetTotalInterest">₹50,37,280</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-white/20">
                    <span class="text-white/90 font-bold">Total Payment</span>
                    <span class="font-black text-sm" id="widgetTotalAmount">₹1,00,37,280</span>
                </div>
            </div>
        </div>

        <!-- Compact Chart -->
        <div class="bg-slate-50 rounded-2xl p-5">
            <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-secondary text-xs"></i>
                Payment Breakdown
            </h4>
            <div class="flex items-center justify-center">
                <canvas id="widgetEmiChart" width="200" height="200"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-4">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                    <div>
                        <p class="text-xs text-slate-500">Principal</p>
                        <p class="text-sm font-bold text-slate-900" id="widgetPrincipalPercent">50%</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div>
                        <p class="text-xs text-slate-500">Interest</p>
                        <p class="text-sm font-bold text-slate-900" id="widgetInterestPercent">50%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-lightbulb text-blue-600 text-lg mt-0.5"></i>
                <div>
                    <h5 class="text-sm font-bold text-blue-900 mb-1">How EMI is Calculated</h5>
                    <p class="text-xs text-blue-700 leading-relaxed">
                        EMI = [P × R × (1+R)^N] / [(1+R)^N - 1]<br>
                        <span class="text-blue-600">P = Principal, R = Monthly Rate, N = Months</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backdrop Overlay -->
<div id="emiBackdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 opacity-0 invisible transition-all duration-300"></div>

<!-- Chart.js Library (only load once) -->
<script>
    if (typeof Chart === 'undefined') {
        const chartScript = document.createElement('script');
        chartScript.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        document.head.appendChild(chartScript);
    }
</script>

<script>
    // EMI Calculator Widget
    (function() {
        let widgetChart = null;
        const sidebar = document.getElementById('emiSidebar');
        const backdrop = document.getElementById('emiBackdrop');
        const triggerBtn = document.getElementById('emiTriggerBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');

        function openSidebar() {
            sidebar.classList.remove('translate-x-full');
            sidebar.classList.add('translate-x-0');
            backdrop.classList.remove('invisible', 'opacity-0');
            backdrop.classList.add('visible', 'opacity-100');
            document.body.style.overflow = 'hidden';
            
            // Initialize chart after opening
            setTimeout(() => {
                if (!widgetChart) calculateWidgetEMI();
            }, 100);
        }

        function closeSidebar() {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('translate-x-full');
            backdrop.classList.remove('visible', 'opacity-100');
            backdrop.classList.add('invisible', 'opacity-0');
            document.body.style.overflow = '';
        }

        triggerBtn.addEventListener('click', openSidebar);
        closeBtn.addEventListener('click', closeSidebar);
        backdrop.addEventListener('click', closeSidebar);

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('translate-x-0')) {
                closeSidebar();
            }
        });

        function formatCurrency(amount) {
            if (amount >= 10000000) {
                return (amount / 10000000).toFixed(2) + ' Cr';
            } else if (amount >= 100000) {
                return (amount / 100000).toFixed(2) + ' L';
            } else {
                return amount.toLocaleString('en-IN');
            }
        }

        function calculateWidgetEMI() {
            const principal = parseFloat(document.getElementById('widgetLoanAmount').value);
            const annualRate = parseFloat(document.getElementById('widgetInterestRate').value);
            const years = parseInt(document.getElementById('widgetTenure').value);

            // Update displays
            document.getElementById('widgetLoanAmountDisplay').textContent = formatCurrency(principal);
            document.getElementById('widgetInterestRateDisplay').textContent = annualRate.toFixed(1);
            document.getElementById('widgetTenureDisplay').textContent = years;

            // Calculate EMI
            const monthlyRate = annualRate / 12 / 100;
            const months = years * 12;
            
            let emi = 0;
            if (monthlyRate === 0) {
                emi = principal / months;
            } else {
                const numerator = principal * monthlyRate * Math.pow(1 + monthlyRate, months);
                const denominator = Math.pow(1 + monthlyRate, months) - 1;
                emi = numerator / denominator;
            }

            const totalAmount = emi * months;
            const totalInterest = totalAmount - principal;

            // Update UI
            document.getElementById('widgetEmiAmount').textContent = '₹' + Math.round(emi).toLocaleString('en-IN');
            document.getElementById('widgetPrincipalAmount').textContent = '₹' + formatCurrency(principal);
            document.getElementById('widgetTotalInterest').textContent = '₹' + formatCurrency(totalInterest);
            document.getElementById('widgetTotalAmount').textContent = '₹' + formatCurrency(totalAmount);

            // Calculate percentages
            const principalPercent = ((principal / totalAmount) * 100).toFixed(1);
            const interestPercent = ((totalInterest / totalAmount) * 100).toFixed(1);
            document.getElementById('widgetPrincipalPercent').textContent = principalPercent + '%';
            document.getElementById('widgetInterestPercent').textContent = interestPercent + '%';

            // Update Chart
            updateWidgetChart(principal, totalInterest);
        }

        function updateWidgetChart(principal, interest) {
            // Wait for Chart.js to load
            if (typeof Chart === 'undefined') {
                setTimeout(() => updateWidgetChart(principal, interest), 100);
                return;
            }

            const ctx = document.getElementById('widgetEmiChart');
            if (!ctx) return;
            
            if (widgetChart) {
                widgetChart.destroy();
            }

            widgetChart = new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Principal', 'Interest'],
                    datasets: [{
                        data: [principal, interest],
                        backgroundColor: ['#3B82F6', '#EF4444'],
                        borderWidth: 0,
                        hoverOffset: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ₹' + context.parsed.toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }

        // Event Listeners
        document.getElementById('widgetLoanAmount').addEventListener('input', calculateWidgetEMI);
        document.getElementById('widgetInterestRate').addEventListener('input', calculateWidgetEMI);
        document.getElementById('widgetTenure').addEventListener('input', calculateWidgetEMI);

        // Initial calculation
        calculateWidgetEMI();
    })();
</script>

<style>
    /* Widget Slider Styling */
    .widget-slider::-webkit-slider-thumb {
        appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #EAB308;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(234, 179, 8, 0.4);
        transition: all 0.3s ease;
    }

    .widget-slider::-webkit-slider-thumb:hover {
        transform: scale(1.15);
        box-shadow: 0 4px 12px rgba(234, 179, 8, 0.6);
    }

    .widget-slider::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #EAB308;
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 8px rgba(234, 179, 8, 0.4);
        transition: all 0.3s ease;
    }

    .widget-slider::-moz-range-thumb:hover {
        transform: scale(1.15);
        box-shadow: 0 4px 12px rgba(234, 179, 8, 0.6);
    }

    /* Smooth scrollbar for sidebar */
    #emiSidebar::-webkit-scrollbar {
        width: 6px;
    }

    #emiSidebar::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    #emiSidebar::-webkit-scrollbar-thumb {
        background: #EAB308;
        border-radius: 10px;
    }

    #emiSidebar::-webkit-scrollbar-thumb:hover {
        background: #ca9a07;
    }
</style>
