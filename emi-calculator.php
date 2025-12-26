<?php
require_once __DIR__ . '/includes/header.php';
?>

<!-- EMI Calculator Section -->
<section class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 py-20 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, gold 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>
    
    <!-- Glow Effects -->
    <div class="absolute top-1/4 left-0 w-96 h-96 bg-secondary/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-1/4 right-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
    
    <div class="container mx-auto px-6 relative z-10">
        <!-- Header -->
        <div class="text-center mb-12" data-aos="fade-up">
            <div class="inline-flex items-center gap-2 bg-secondary/10 backdrop-blur-sm text-secondary px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider mb-4 border border-secondary/20">
                <i class="fa-solid fa-calculator"></i> Financial Planning
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4 font-display">
                EMI Calculator
            </h1>
            <p class="text-slate-400 text-lg max-w-2xl mx-auto">
                Calculate your monthly loan payments and plan your home purchase with precision.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 max-w-7xl mx-auto">
            <!-- Left: Input Section -->
            <div class="lg:col-span-2 bg-white/95 backdrop-blur-sm rounded-2xl p-8 shadow-2xl border border-white/20" data-aos="fade-right">
                <h2 class="text-2xl font-bold text-slate-900 mb-6 font-display flex items-center gap-3">
                    <i class="fa-solid fa-sliders text-secondary"></i>
                    Loan Details
                </h2>
                
                <!-- Loan Amount -->
                <div class="mb-8">
                    <label class="flex items-center justify-between mb-3">
                        <span class="text-sm font-bold text-slate-700 uppercase tracking-wide">Loan Amount</span>
                        <span class="text-lg font-black text-secondary">₹<span id="loanAmountDisplay">50,00,000</span></span>
                    </label>
                    <input type="range" id="loanAmount" min="100000" max="50000000" step="100000" value="5000000" 
                           class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer slider">
                    <div class="flex justify-between text-xs text-slate-400 mt-2">
                        <span>₹1L</span>
                        <span>₹5Cr</span>
                    </div>
                </div>

                <!-- Interest Rate -->
                <div class="mb-8">
                    <label class="flex items-center justify-between mb-3">
                        <span class="text-sm font-bold text-slate-700 uppercase tracking-wide">Interest Rate (p.a.)</span>
                        <span class="text-lg font-black text-secondary"><span id="interestRateDisplay">8.5</span>%</span>
                    </label>
                    <input type="range" id="interestRate" min="5" max="20" step="0.1" value="8.5" 
                           class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer slider">
                    <div class="flex justify-between text-xs text-slate-400 mt-2">
                        <span>5%</span>
                        <span>20%</span>
                    </div>
                </div>

                <!-- Loan Tenure -->
                <div class="mb-8">
                    <label class="flex items-center justify-between mb-3">
                        <span class="text-sm font-bold text-slate-700 uppercase tracking-wide">Loan Tenure</span>
                        <span class="text-lg font-black text-secondary"><span id="tenureDisplay">20</span> Years</span>
                    </label>
                    <input type="range" id="tenure" min="1" max="30" step="1" value="20" 
                           class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer slider">
                    <div class="flex justify-between text-xs text-slate-400 mt-2">
                        <span>1 Year</span>
                        <span>30 Years</span>
                    </div>
                </div>

                <!-- Manual Input Toggle -->
                <div class="pt-6 border-t border-slate-200">
                    <button onclick="toggleManualInput()" class="text-sm text-secondary hover:text-yellow-600 transition font-bold flex items-center gap-2">
                        <i class="fa-solid fa-keyboard"></i>
                        <span id="manualToggleText">Enter values manually</span>
                    </button>
                    
                    <div id="manualInputs" class="hidden mt-4 space-y-3">
                        <input type="number" id="manualLoan" placeholder="Loan Amount" 
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-secondary text-sm">
                        <input type="number" id="manualRate" placeholder="Interest Rate %" step="0.1"
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-secondary text-sm">
                        <input type="number" id="manualTenure" placeholder="Tenure (Years)"
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-secondary text-sm">
                        <button onclick="applyManualInputs()" class="w-full bg-secondary text-white py-2 rounded-lg hover:bg-yellow-600 transition font-bold text-sm">
                            Apply Values
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Results Section -->
            <div class="lg:col-span-3 space-y-6" data-aos="fade-left">
                <!-- EMI Card -->
                <div class="bg-gradient-to-br from-secondary to-yellow-600 rounded-2xl p-8 shadow-2xl text-white">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                            <i class="fa-solid fa-indian-rupee-sign text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-white/80 text-sm font-medium">Monthly EMI</p>
                            <h3 class="text-4xl font-black font-display" id="emiAmount">₹41,822</h3>
                        </div>
                    </div>
                    <p class="text-white/70 text-xs">This is your monthly payment for the loan</p>
                </div>

                <!-- Breakdown Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white/95 backdrop-blur-sm rounded-xl p-6 shadow-lg border border-white/20">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
                                <i class="fa-solid fa-wallet text-blue-600"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Principal</p>
                        </div>
                        <p class="text-2xl font-black text-slate-900" id="principalAmount">₹50,00,000</p>
                    </div>

                    <div class="bg-white/95 backdrop-blur-sm rounded-xl p-6 shadow-lg border border-white/20">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center">
                                <i class="fa-solid fa-percent text-red-600"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Interest</p>
                        </div>
                        <p class="text-2xl font-black text-slate-900" id="totalInterest">₹50,37,280</p>
                    </div>

                    <div class="bg-white/95 backdrop-blur-sm rounded-xl p-6 shadow-lg border border-white/20">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center">
                                <i class="fa-solid fa-money-bill-trend-up text-green-600"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Total</p>
                        </div>
                        <p class="text-2xl font-black text-slate-900" id="totalAmount">₹1,00,37,280</p>
                    </div>
                </div>

                <!-- Chart -->
                <div class="bg-white/95 backdrop-blur-sm rounded-2xl p-8 shadow-lg border border-white/20">
                    <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-secondary"></i>
                        Payment Breakdown
                    </h3>
                    <div class="flex items-center justify-center">
                        <canvas id="emiChart" width="300" height="300"></canvas>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded-full bg-blue-500"></div>
                            <div>
                                <p class="text-xs text-slate-500">Principal Amount</p>
                                <p class="text-sm font-bold text-slate-900" id="principalPercent">50%</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded-full bg-red-500"></div>
                            <div>
                                <p class="text-xs text-slate-500">Total Interest</p>
                                <p class="text-sm font-bold text-slate-900" id="interestPercent">50%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Amortization Schedule -->
                <div class="bg-white/95 backdrop-blur-sm rounded-2xl p-8 shadow-lg border border-white/20">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-table text-secondary"></i>
                            Amortization Schedule (First 12 Months)
                        </h3>
                        <button onclick="toggleFullSchedule()" class="text-xs text-secondary hover:text-yellow-600 transition font-bold">
                            View Full Schedule
                        </button>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b-2 border-slate-200">
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase">Month</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 uppercase">EMI</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 uppercase">Principal</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 uppercase">Interest</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 uppercase">Balance</th>
                                </tr>
                            </thead>
                            <tbody id="scheduleTable">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="mt-16 max-w-4xl mx-auto" data-aos="fade-up">
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-8">
                <h3 class="text-2xl font-bold text-white mb-6 font-display flex items-center gap-3">
                    <i class="fa-solid fa-circle-info text-secondary"></i>
                    How EMI is Calculated
                </h3>
                <div class="text-slate-300 space-y-4 text-sm leading-relaxed">
                    <p>EMI (Equated Monthly Installment) is calculated using the following formula:</p>
                    <div class="bg-white/10 backdrop-blur-sm p-6 rounded-xl border border-white/20 font-mono text-center">
                        <p class="text-lg">EMI = [P × R × (1+R)^N] / [(1+R)^N - 1]</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div class="bg-white/5 p-4 rounded-xl">
                            <p class="font-bold text-secondary mb-1">P = Principal</p>
                            <p class="text-xs text-slate-400">Total loan amount</p>
                        </div>
                        <div class="bg-white/5 p-4 rounded-xl">
                            <p class="font-bold text-secondary mb-1">R = Rate</p>
                            <p class="text-xs text-slate-400">Monthly interest rate (Annual Rate / 12 / 100)</p>
                        </div>
                        <div class="bg-white/5 p-4 rounded-xl">
                            <p class="font-bold text-secondary mb-1">N = Tenure</p>
                            <p class="text-xs text-slate-400">Total months (Years × 12)</p>
                        </div>
                    </div>
                    <p class="pt-4">
                        <strong>Example:</strong> For a loan of ₹50,00,000 at 8.5% interest for 20 years:
                    </p>
                    <ul class="list-disc list-inside space-y-2 pl-4">
                        <li>Monthly Interest Rate (R) = 8.5 / 12 / 100 = 0.00708</li>
                        <li>Total Months (N) = 20 × 12 = 240</li>
                        <li>EMI = [5000000 × 0.00708 × (1.00708)^240] / [(1.00708)^240 - 1]</li>
                        <li>EMI ≈ ₹41,822 per month</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- AOS Animation -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });
</script>

<script>
    // EMI Calculation
    let chart = null;

    function formatCurrency(amount) {
        if (amount >= 10000000) {
            return (amount / 10000000).toFixed(2) + ' Cr';
        } else if (amount >= 100000) {
            return (amount / 100000).toFixed(2) + ' L';
        } else {
            return amount.toLocaleString('en-IN');
        }
    }

    function calculateEMI() {
        const principal = parseFloat(document.getElementById('loanAmount').value);
        const annualRate = parseFloat(document.getElementById('interestRate').value);
        const years = parseInt(document.getElementById('tenure').value);

        // Update displays
        document.getElementById('loanAmountDisplay').textContent = formatCurrency(principal);
        document.getElementById('interestRateDisplay').textContent = annualRate.toFixed(1);
        document.getElementById('tenureDisplay').textContent = years;

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
        document.getElementById('emiAmount').textContent = '₹' + Math.round(emi).toLocaleString('en-IN');
        document.getElementById('principalAmount').textContent = '₹' + formatCurrency(principal);
        document.getElementById('totalInterest').textContent = '₹' + formatCurrency(totalInterest);
        document.getElementById('totalAmount').textContent = '₹' + formatCurrency(totalAmount);

        // Calculate percentages
        const principalPercent = ((principal / totalAmount) * 100).toFixed(1);
        const interestPercent = ((totalInterest / totalAmount) * 100).toFixed(1);
        document.getElementById('principalPercent').textContent = principalPercent + '%';
        document.getElementById('interestPercent').textContent = interestPercent + '%';

        // Update Chart
        updateChart(principal, totalInterest);

        // Update Amortization Schedule
        updateSchedule(principal, monthlyRate, months, emi);
    }

    function updateChart(principal, interest) {
        const ctx = document.getElementById('emiChart').getContext('2d');
        
        if (chart) {
            chart.destroy();
        }

        chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Principal Amount', 'Total Interest'],
                datasets: [{
                    data: [principal, interest],
                    backgroundColor: ['#3B82F6', '#EF4444'],
                    borderWidth: 0,
                    hoverOffset: 10
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
                cutout: '70%'
            }
        });
    }

    function updateSchedule(principal, monthlyRate, totalMonths, emi) {
        const scheduleTable = document.getElementById('scheduleTable');
        scheduleTable.innerHTML = '';

        let balance = principal;
        const displayMonths = Math.min(12, totalMonths); // Show first 12 months

        for (let month = 1; month <= displayMonths; month++) {
            const interestPayment = balance * monthlyRate;
            const principalPayment = emi - interestPayment;
            balance -= principalPayment;

            const row = document.createElement('tr');
            row.className = 'border-b border-slate-100 hover:bg-slate-50 transition';
            row.innerHTML = `
                <td class="px-4 py-3 text-slate-700 font-medium">${month}</td>
                <td class="px-4 py-3 text-right text-slate-900 font-semibold">₹${Math.round(emi).toLocaleString('en-IN')}</td>
                <td class="px-4 py-3 text-right text-blue-600">₹${Math.round(principalPayment).toLocaleString('en-IN')}</td>
                <td class="px-4 py-3 text-right text-red-600">₹${Math.round(interestPayment).toLocaleString('en-IN')}</td>
                <td class="px-4 py-3 text-right text-slate-900 font-bold">₹${Math.round(balance).toLocaleString('en-IN')}</td>
            `;
            scheduleTable.appendChild(row);
        }
    }

    function toggleManualInput() {
        const manualInputs = document.getElementById('manualInputs');
        const toggleText = document.getElementById('manualToggleText');
        
        if (manualInputs.classList.contains('hidden')) {
            manualInputs.classList.remove('hidden');
            toggleText.textContent = 'Use sliders instead';
        } else {
            manualInputs.classList.add('hidden');
            toggleText.textContent = 'Enter values manually';
        }
    }

    function applyManualInputs() {
        const loanAmount = parseFloat(document.getElementById('manualLoan').value);
        const interestRate = parseFloat(document.getElementById('manualRate').value);
        const tenure = parseInt(document.getElementById('manualTenure').value);

        if (loanAmount && interestRate && tenure) {
            document.getElementById('loanAmount').value = loanAmount;
            document.getElementById('interestRate').value = interestRate;
            document.getElementById('tenure').value = tenure;
            calculateEMI();
            toggleManualInput();
        } else {
            alert('Please enter all values');
        }
    }

    function toggleFullSchedule() {
        alert('Full schedule view coming soon! This will show the complete amortization schedule for all ' + 
              (parseInt(document.getElementById('tenure').value) * 12) + ' months.');
    }

    // Event Listeners
    document.getElementById('loanAmount').addEventListener('input', calculateEMI);
    document.getElementById('interestRate').addEventListener('input', calculateEMI);
    document.getElementById('tenure').addEventListener('input', calculateEMI);

    // Initial calculation
    calculateEMI();
</script>

<style>
    /* Custom Slider Styling */
    .slider::-webkit-slider-thumb {
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #EAB308;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(234, 179, 8, 0.4);
        transition: all 0.3s ease;
    }

    .slider::-webkit-slider-thumb:hover {
        transform: scale(1.2);
        box-shadow: 0 4px 12px rgba(234, 179, 8, 0.6);
    }

    .slider::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #EAB308;
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 8px rgba(234, 179, 8, 0.4);
        transition: all 0.3s ease;
    }

    .slider::-moz-range-thumb:hover {
        transform: scale(1.2);
        box-shadow: 0 4px 12px rgba(234, 179, 8, 0.6);
    }

    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #EAB308;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #ca9a07;
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
