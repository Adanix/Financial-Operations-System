<x-layouts.app>
    <x-slot:header>Documentation & Operational Guide</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-8 pb-12">
        <!-- Hero Header -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 sm:p-8 shadow-xs">
            <div class="flex items-center gap-3 mb-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                    SoloLedger v1.0
                </span>
                <span class="text-xs text-[#66736C]">In-App Operational Reference Manual</span>
            </div>
            <h1 class="text-2xl font-bold text-[#17211D]">SoloLedger Financial Architecture & System Guide</h1>
            <p class="text-sm text-[#66736C] mt-2 leading-relaxed">
                A purpose-built, single-business financial operations system designed for freelancers, independent consultants, and boutique service firms. This document details core architectural rules, calculation methodologies, and user roles.
            </p>
        </div>

        <!-- Quick Table of Contents -->
        <div class="bg-slate-50 border border-[#E3E7E4] rounded-2xl p-6">
            <h2 class="text-xs font-bold text-[#17211D] uppercase tracking-wider mb-3">Table of Contents</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                <a href="#section-1" class="text-[#0F6B57] hover:underline">1. SoloLedger Purpose & Product Boundaries</a>
                <a href="#section-2" class="text-[#0F6B57] hover:underline">2. Demo Mode & Role-Based Access Control</a>
                <a href="#section-3" class="text-[#0F6B57] hover:underline">3. Dashboard Key Performance Indicators (KPIs)</a>
                <a href="#section-4" class="text-[#0F6B57] hover:underline">4. Customer Management & Billing Profiles</a>
                <a href="#section-5" class="text-[#0F6B57] hover:underline">5. Invoice Lifecycle, Sequences & Immutable Snapshots</a>
                <a href="#section-6" class="text-[#0F6B57] hover:underline">6. Tax Pricing Modes & Mathematical Rounding</a>
                <a href="#section-7" class="text-[#0F6B57] hover:underline">7. PDF Layout & Bank Remittance Coordinates</a>
                <a href="#section-8" class="text-[#0F6B57] hover:underline">8. Invoice Email Dispatch & Status Log</a>
                <a href="#section-9" class="text-[#0F6B57] hover:underline">9. Payment Recording, Locks & Reversals</a>
                <a href="#section-10" class="text-[#0F6B57] hover:underline">10. Operating Expenses, Tax Treatment & Receipts</a>
                <a href="#section-11" class="text-[#0F6B57] hover:underline">11. Tax Overview & Statutory Filing Limitations</a>
                <a href="#section-12" class="text-[#0F6B57] hover:underline">12. Reporting Methodology & Date Bases</a>
                <a href="#section-13" class="text-[#0F6B57] hover:underline">13. Security Architecture & Audit Trail</a>
                <a href="#section-14" class="text-[#0F6B57] hover:underline">14. Data Export, Backup & Scope Limits</a>
            </div>
        </div>

        <!-- Main Content Sections -->
        <div class="space-y-8 bg-white border border-[#E3E7E4] rounded-2xl p-6 sm:p-10 shadow-xs text-sm text-slate-800 leading-relaxed">

            <!-- Section 1 -->
            <section id="section-1" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">1. SoloLedger Purpose & Product Boundaries</h2>
                <p>
                    SoloLedger provides a streamlined, opinionated financial ledger focused strictly on the needs of single-business operators. It bridges client invoicing, payment collection, operating expense tracking, and management profitability without the operational overhead of heavy ERP software.
                </p>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-1">
                    <p class="font-bold text-[#17211D]">Explicit Product Boundaries:</p>
                    <ul class="list-disc pl-5 space-y-1 mt-1">
                        <li><strong>Single Business Entity:</strong> Supports one primary business profile with dedicated bank remittance coordinates.</li>
                        <li><strong>Single Functional Currency:</strong> Default currency with standardized decimal handling (0 or 2 decimal places). No multi-currency FX conversion.</li>
                        <li><strong>Exclusions:</strong> Excludes full double-entry general ledger bookkeeping, physical inventory/warehousing, automated employee payroll, and synchronous merchant payment gateway processing.</li>
                    </ul>
                </div>
            </section>

            <!-- Section 2 -->
            <section id="section-2" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">2. Demo Mode & Role-Based Access Control</h2>
                <p>
                    SoloLedger enforces strict role segregation through two authenticated roles:
                </p>
                <ul class="list-disc pl-5 space-y-1 text-xs text-slate-700">
                    <li><strong>Owner:</strong> Complete administrative and write privileges. Can create, edit, issue, void invoices, record and reverse payments, record expenses, modify tax rates, configure business identity, and download receipts.</li>
                    <li><strong>Reviewer (Viewer):</strong> Strictly read-only access. May inspect dashboard KPIs, view customer records, preview/download invoice PDFs, analyze financial reports, and inspect audit logs. All mutation requests (POST, PUT, PATCH, DELETE) return HTTP 403 Forbidden.</li>
                </ul>
                <p class="text-xs text-[#66736C]">
                    When <code>APP_DEMO_MODE=true</code> is enabled (in non-production environments), a top utility bar allows rapid switching between Owner and Reviewer. The demo login endpoint rejects requests automatically when <code>APP_ENV=production</code>.
                </p>
            </section>

            <!-- Section 3 -->
            <section id="section-3" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">3. Dashboard Key Performance Indicators (KPIs)</h2>
                <p>The operational dashboard summarizes current business performance across six core metrics:</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl border border-[#E3E7E4]">
                        <span class="font-bold text-[#17211D] block">Revenue (MTD)</span>
                        <span>Net invoiced sales (excluding sales tax) from invoices issued within the current calendar month.</span>
                    </div>
                    <div class="p-3 rounded-xl border border-[#E3E7E4]">
                        <span class="font-bold text-[#17211D] block">Outstanding Receivables</span>
                        <span>Total unpaid balance across all currently issued and partially paid invoices.</span>
                    </div>
                    <div class="p-3 rounded-xl border border-[#E3E7E4]">
                        <span class="font-bold text-[#17211D] block">Overdue Invoices</span>
                        <span>Sum of unpaid balances on invoices whose due date is earlier than today in the configured timezone.</span>
                    </div>
                    <div class="p-3 rounded-xl border border-[#E3E7E4]">
                        <span class="font-bold text-[#17211D] block">Expenses (MTD)</span>
                        <span>Operating expense burden recognized in the current month (net of recoverable input taxes).</span>
                    </div>
                    <div class="p-3 rounded-xl border border-[#E3E7E4]">
                        <span class="font-bold text-[#17211D] block">Net Cash Flow (MTD)</span>
                        <span>Actual cash collected minus actual cash expenses paid between the 1st of the month and today.</span>
                    </div>
                    <div class="p-3 rounded-xl border border-[#E3E7E4]">
                        <span class="font-bold text-[#17211D] block">Estimated Tax Position</span>
                        <span>Net output tax collected on sales minus eligible recoverable input tax paid on operating costs.</span>
                    </div>
                </div>
            </section>

            <!-- Section 4 -->
            <section id="section-4" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">4. Customer Management & Billing Profiles</h2>
                <p>
                    Customers represent billing entities. Each customer profile maintains contact details, physical/billing address, and tax registration identifiers. Active customers can be selected during invoice creation. Deactivating a customer hides them from future invoice creation while fully preserving all historical invoice associations.
                </p>
            </section>

            <!-- Section 5 -->
            <section id="section-5" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">5. Invoice Lifecycle, Sequences & Immutable Snapshots</h2>
                <p>
                    Invoices traverse a deterministic lifecycle state machine:
                    <code class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 font-mono text-xs">draft &rarr; issued &rarr; partially_paid &rarr; paid</code> (with optional <code class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 font-mono text-xs">void</code>).
                </p>
                <div class="space-y-2 text-xs">
                    <p><strong>Draft State:</strong> Line items, quantities, discounts, and customer details can be freely modified or deleted. Drafts do not consume sequence numbers.</p>
                    <p><strong>Issuance & Atomic Numbering:</strong> When an invoice is issued, the system executes an atomic transaction with pessimistic row-locking on the sequence ledger to assign the next serial number formatted as <code>{PREFIX}-{YEAR}-{SEQUENCE}</code> (e.g. <code>INV-2026-0001</code>). Gaps are avoided under concurrent requests.</p>
                    <p><strong>Immutable Snapshots:</strong> Upon issuance, the complete business profile, customer billing coordinates, and bank details are frozen into JSON snapshots. Historical invoices never reflect future updates to business name, address, or customer tax IDs.</p>
                    <p><strong>Voiding Rules:</strong> An issued invoice may only be voided if zero payments are attached. If payments were recorded, all payments must first be formally reversed before voiding is permitted.</p>
                </div>
            </section>

            <!-- Section 6 -->
            <section id="section-6" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">6. Tax Pricing Modes & Mathematical Rounding</h2>
                <p>
                    SoloLedger supports both <strong>Tax-Exclusive</strong> and <strong>Tax-Inclusive</strong> pricing treatments with exact decimal arithmetic via <code>BigDecimal</code> and half-up rounding (no floating-point inaccuracy):
                </p>
                <ul class="list-disc pl-5 space-y-1 text-xs text-slate-700">
                    <li><strong>Exclusive Pricing:</strong> Tax is added on top of the taxable subtotal. Line Subtotal = Quantity &times; Unit Price &minus; Line Discount. Tax = Line Subtotal &times; Rate. Total = Subtotal + Tax.</li>
                    <li><strong>Inclusive Pricing:</strong> Line gross total includes tax. Tax is extracted using: <code>Tax = Gross - (Gross / (1 + Rate))</code>.</li>
                    <li><strong>Invoice-Level Discounts:</strong> Proportional discount allocation across line items uses the largest-remainder method to guarantee that the sum of allocated line discounts exactly matches the total invoice discount with zero penny discrepancies.</li>
                </ul>
            </section>

            <!-- Section 7 -->
            <section id="section-7" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">7. PDF Layout & Bank Remittance Coordinates</h2>
                <p>
                    Invoice PDFs are rendered server-side using Dompdf on standard A4 format. Draft invoices display a diagonal light watermark ("DRAFT — NOT AN INVOICE"). Issued invoices omit the watermark and render immutable customer and business snapshots, itemized lines, tax breakdown, and clear bank transfer instructions (Bank Name, Account Holder, IBAN/Account Number, SWIFT/BIC, and Remittance Reference).
                </p>
            </section>

            <!-- Section 8 -->
            <section id="section-8" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">8. Invoice Email Dispatch & Status Log</h2>
                <p>
                    Issued invoices can be dispatched directly to customer email addresses with the PDF attached. Every dispatch attempt writes an entry to the <code>invoice_email_logs</code> table:
                </p>
                <ul class="list-disc pl-5 space-y-1 text-xs text-slate-700">
                    <li><code>sent</code>: Email was successfully transmitted through configured SMTP transport.</li>
                    <li><code>sent_demo</code>: In demo mode, email transmission is simulated; the event is recorded in the activity log without dispatching network packets.</li>
                    <li><code>failed</code>: SMTP transport error was caught, and error details were logged.</li>
                </ul>
            </section>

            <!-- Section 9 -->
            <section id="section-9" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">9. Payment Recording, Locks & Reversals</h2>
                <p>
                    Multiple partial or full payments can be logged against any issued invoice. 
                </p>
                <ul class="list-disc pl-5 space-y-1 text-xs text-slate-700">
                    <li><strong>Overpayment Protection:</strong> Payments are acquired under database row locks. Any attempt to record a payment exceeding the outstanding balance is rejected.</li>
                    <li><strong>Reversal Workflow:</strong> Completed payments cannot be deleted. Instead, an Owner may reverse a payment with a mandatory audit reason. The reversal sets payment status to <code>reversed</code>, logs the reversal timestamp, restores the invoice outstanding balance, and shifts the invoice status from <code>paid</code> back to <code>partially_paid</code> or <code>issued</code>.</li>
                </ul>
            </section>

            <!-- Section 10 -->
            <section id="section-10" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">10. Operating Expenses, Tax Treatment & Receipts</h2>
                <p>
                    Operating expenses are tracked with vendor, date, category, and payment status (paid/unpaid). Tax handling follows international value-added standards:
                </p>
                <ul class="list-disc pl-5 space-y-1 text-xs text-slate-700">
                    <li><strong>Recoverable Input Tax:</strong> If marked recoverable, the purchase tax is claimed as an input tax credit, and only the net expense enters the Management P&L.</li>
                    <li><strong>Non-Recoverable Tax:</strong> If marked non-recoverable, the full gross amount enters P&L operating expenses.</li>
                    <li><strong>Private Storage:</strong> Receipt attachments (PDF/Images) are stored on private non-public disk storage and served exclusively through authenticated routes with Owner authorization.</li>
                </ul>
            </section>

            <!-- Section 11 -->
            <section id="section-11" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">11. Tax Overview & Statutory Filing Limitations</h2>
                <p>
                    The Tax Overview summarizes output tax (accrual basis on issued invoices) and recoverable input tax (on recorded expenses) for any selected period. The net position indicates estimated tax payable or refund claimable.
                </p>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
                    <strong>Statutory Disclaimer:</strong> SoloLedger provides operational management figures. It does not replace official statutory tax returns, jurisdiction-specific withholding schemes, or professional accounting counsel.
                </div>
            </section>

            <!-- Section 12 -->
            <section id="section-12" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">12. Reporting Methodology & Date Bases</h2>
                <p>SoloLedger includes five dedicated operational reports:</p>
                <ol class="list-decimal pl-5 space-y-1 text-xs text-slate-700">
                    <li><strong>Management P&L:</strong> Accrual net revenue (by invoice issue date) minus net operating expenses. Includes a non-GAAP cash flow memo showing actual cash collections and disbursements.</li>
                    <li><strong>Tax Summary:</strong> Aggregates output tax and input tax broken down by individual tax rate bracket.</li>
                    <li><strong>Revenue & Sales:</strong> Gross and net invoiced sales itemized by customer and invoice issue date.</li>
                    <li><strong>Expense Breakdown:</strong> Operating expenses categorized with percentage shares and transaction counts.</li>
                    <li><strong>Aged Receivables:</strong> Historical aging reconstruction based on an exact <em>As-of Date</em>, placing invoices into Current, 1–30, 31–60, 61–90, and 90+ day overdue buckets based on payments completed as of that date.</li>
                </ol>
            </section>

            <!-- Section 13 -->
            <section id="section-13" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">13. Security Architecture & Audit Trail</h2>
                <p>
                    Security controls include CSRF protection on all mutating HTTP verbs, prepared SQL queries via Eloquent ORM, strict authorization middleware, and automated formula injection sanitization on CSV exports (prepending single quotes to fields starting with <code>=</code>, <code>+</code>, <code>-</code>, or <code>@</code>). An immutable, append-only activity log records all critical actions (invoicing, payments, reversals, setting edits).
                </p>
            </section>

            <!-- Section 14 -->
            <section id="section-14" class="space-y-3">
                <h2 class="text-base font-bold text-[#17211D] border-b border-[#E3E7E4] pb-2">14. Data Export, Backup & Scope Limits</h2>
                <p>
                    Reports and ledger data can be exported to standard CSV at any time. For full system backup, administrators can back up the MySQL database and the <code>storage/app/receipts</code> directory.
                </p>
            </section>

            <!-- Required Extensibility Notice -->
            <div class="mt-8 pt-6 border-t border-[#E3E7E4]">
                <div class="p-5 rounded-2xl bg-[#F6F7F5] border border-[#E3E7E4]">
                    <h3 class="text-xs font-bold text-[#17211D] uppercase tracking-wider mb-2">Custom Development & Future Extensibility</h3>
                    <p class="text-xs text-slate-700 leading-relaxed italic">
                        SoloLedger is built on a modular foundation and can be adapted to additional business requirements. Future enhancements may include recurring invoices, customer statements, invoice reminders, payment gateway integrations, multi-currency support, additional staff roles, jurisdiction-specific tax workflows, and customized document templates. These capabilities are not included in the current release unless separately implemented and tested.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
