<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="bg-primary text-white py-24 relative overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-secondary/10 rounded-full -mr-48 -mt-48 blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-secondary/5 rounded-full -ml-32 -mb-32 blur-2xl"></div>

    <div class="container mx-auto px-6 relative z-10 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6 font-display" data-aos="fade-up">Get in Touch</h1>
        <p class="text-slate-300 text-lg max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            Have questions about a property or need expert advice? Our team is here to help you navigate the Gurgaon real estate market.
        </p>
    </div>
</section>

<section class="py-20 -mt-16 relative z-20">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contact Info Cards -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-8 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 transition hover:translate-y- -1" data-aos="fade-up">
                    <div class="w-12 h-12 bg-secondary/10 rounded-2xl flex items-center justify-center text-secondary mb-6 text-xl">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Our Office</h3>
                    <p class="text-slate-500 leading-relaxed">
                        123 Business Avenue, Sector 44,<br>
                        Gurgaon, Haryana 122003
                    </p>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 transition hover:translate-y- -1" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-12 h-12 bg-secondary/10 rounded-2xl flex items-center justify-center text-secondary mb-6 text-xl">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Call Us</h3>
                    <p class="text-slate-500 mb-1">General Inquiries: +91 98765 43210</p>
                    <p class="text-slate-500">Support: +91 0124 456789</p>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 transition hover:translate-y- -1" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-12 h-12 bg-secondary/10 rounded-2xl flex items-center justify-center text-secondary mb-6 text-xl">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Email Us</h3>
                    <p class="text-slate-500">hello@gurgaonproperty.com</p>
                    <p class="text-slate-500">sales@gurgaonproperty.com</p>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <div class="bg-white p-8 md:p-12 rounded-3xl shadow-2xl shadow-slate-200/60 border border-slate-50" data-aos="fade-left">
                    <h2 class="text-3xl font-bold text-slate-800 mb-8 font-display">Send us a Message</h2>
                    
                    <form id="contactForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-600 ml-1">Full Name</label>
                                <input type="text" name="name" required placeholder="John Doe" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-600 ml-1">Email Address</label>
                                <input type="email" name="email" required placeholder="john@example.com" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-600 ml-1">Phone Number</label>
                                <input type="tel" name="phone" placeholder="+91 XXXXX XXXXX" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-600 ml-1">Reason for Contact</label>
                                <select name="lead_type" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition cursor-pointer">
                                    <option value="Inquiry">General Inquiry</option>
                                    <option value="Buying">Buying Property</option>
                                    <option value="Selling">Selling Property</option>
                                    <option value="Partnership">Partnership</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600 ml-1">Your Message</label>
                            <textarea name="message" rows="5" required placeholder="Tell us how we can help you..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition resize-none"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-5 rounded-2xl hover:bg-secondary transition-all duration-300 shadow-xl shadow-slate-900/10 hover:shadow-secondary/20 flex items-center justify-center gap-3">
                            Send Message <i class="fa-solid fa-paper-plane text-sm"></i>
                        </button>
                    </form>

                    <div id="formResponse" class="mt-6 hidden p-5 rounded-2xl text-center font-medium"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button');
    const responseDiv = document.getElementById('formResponse');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sending...';
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Using existing submit_lead API
    fetch('api/submit_lead.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        responseDiv.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'bg-green-50', 'text-green-700');
        if (res.status === 'success') {
            responseDiv.classList.add('bg-green-50', 'text-green-700');
            responseDiv.innerHTML = '<i class="fa-solid fa-check-circle mr-2"></i> Thank you! Your message has been sent successfully.';
            this.reset();
        } else {
            responseDiv.classList.add('bg-red-50', 'text-red-700');
            responseDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-2"></i> Error: ' + (res.message || 'Something went wrong.');
        }
    })
    .catch(err => {
        responseDiv.classList.add('bg-red-50', 'text-red-700');
        responseDiv.innerHTML = 'Error communicating with server.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Send Message <i class="fa-solid fa-paper-plane text-sm"></i>';
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
