@extends('layouts.legal')

@section('title', 'Privacy Policy | Custom Boxes Experts')
@section('description', 'How Custom Boxes Experts collects, uses and protects information submitted through our rigid box quote request form.')
@section('heading', 'Privacy Policy')
@section('updated', 'Last updated: 4 August 2026. This policy covers the rigid box quote request page at customboxesexperts.com and the information you submit through it.')

@section('footer-links')
<a href="{{ route('home') }}">Rigid boxes</a> &middot; <a href="{{ route('terms') }}">Terms &amp; Conditions</a>
@endsection

@section('legal')
    <h2>Who we are</h2>
        <p>Custom Boxes Experts, 1227 Solano Ave #9, Albany, CA 94706, USA.
        Phone <a href="tel:{{ config('site.phone_e164') }}">(888) 716-1078</a>, email
        <a href="mailto:{{ config('site.email') }}">info@customboxesexperts.com</a>. We manufacture custom rigid and
        printed packaging for businesses in the United States and Canada.</p>

        <h2>Information we collect</h2>
        <h3>Information you give us</h3>
        <ul>
          <li>Your name, email address and phone number.</li>
          <li>Project details: quantity, box style, dimensions, finishing preferences and any notes or artwork you send.</li>
          <li>Anything else you include in an email, phone call or chat with our team.</li>
        </ul>
        <h3>Information collected automatically</h3>
        <ul>
          <li>Standard server log data: IP address, browser type, device type, referring page and timestamps.</li>
          <li>Advertising click identifiers such as Google&rsquo;s <code>gclid</code>, plus UTM campaign parameters present in the page URL. These tell us which ad or keyword brought you here.</li>
          <li>Analytics and advertising cookies set by Google Analytics 4 and Google Ads.</li>
        </ul>

        <h2>How we use it</h2>
        <ul>
          <li>To prepare and send your quote, 3D mockup and dieline.</li>
          <li>To contact you about your inquiry by phone, email or SMS, and to follow up on an open quote.</li>
          <li>To fulfil, produce and ship an order you place.</li>
          <li>To measure which advertising campaigns generate genuine inquiries, and to improve this page.</li>
          <li>To detect and block spam or fraudulent submissions.</li>
        </ul>
        <p>We do not sell your personal information, and we do not share it with third parties for their own marketing.</p>

        <h2>Phone calls, SMS and consent</h2>
        <p>When you submit the quote form you agree that Custom Boxes Experts may contact you by phone, email or text
        message regarding your packaging inquiry. Message frequency varies, and message and data rates may apply.
        Consent to receive calls or texts is <strong>not</strong> a condition of purchase. You can opt out at any time
        by replying STOP to a text, telling the representative on a call, or emailing
        <a href="mailto:{{ config('site.email') }}">info@customboxesexperts.com</a>.</p>

        <h2>Who we share information with</h2>
        <ul>
          <li><strong>Service providers</strong> who operate on our behalf: web hosting, email delivery, CRM and analytics providers, and production or freight partners fulfilling your order. They may only use the data to provide that service.</li>
          <li><strong>Google</strong>, through Google Analytics 4 and Google Ads conversion measurement, so we can attribute inquiries to advertising campaigns.</li>
          <li><strong>Legal authorities</strong>, where we are required by law to disclose information.</li>
        </ul>

        <h2>Cookies and advertising</h2>
        <p>This page uses cookies for analytics and advertising measurement. Google may use these to measure conversions
        and to show you our ads on other sites. You can opt out of personalized Google advertising at
        <a href="https://adssettings.google.com" rel="nofollow noopener">adssettings.google.com</a>, opt out of Google
        Analytics with the <a href="https://tools.google.com/dlpage/gaoptout" rel="nofollow noopener">browser add-on</a>,
        or block cookies in your browser settings. Blocking cookies does not prevent you from requesting a quote.</p>

        <h2>How long we keep it</h2>
        <p>Quote requests are retained for up to 24 months so we can honor reorders and reference prior pricing.
        Order and invoice records are kept as long as tax and accounting law requires. You may ask us to delete your
        data sooner.</p>

        <h2>Your rights</h2>
        <p>Depending on where you live &mdash; including under the California Consumer Privacy Act &mdash; you may have the
        right to access the personal information we hold about you, correct it, request deletion, or opt out of its sale
        or sharing (we do not sell or share personal information as those terms are defined by the CCPA). To exercise any
        of these rights, email <a href="mailto:{{ config('site.email') }}">info@customboxesexperts.com</a> or call
        <a href="tel:{{ config('site.phone_e164') }}">(888) 716-1078</a>. We will respond within 45 days.</p>

        <h2>Security</h2>
        <p>This page is served over HTTPS and submissions are transmitted encrypted. Access to lead data is limited to
        staff who need it to quote and fulfil your project. No method of transmission over the internet is completely
        secure, so we cannot guarantee absolute security.</p>

        <h2>Children</h2>
        <p>This is a business-to-business service. We do not knowingly collect information from anyone under 16.</p>

        <h2>Changes</h2>
        <p>If we change this policy we will update the date at the top of this page. Material changes will be
        highlighted on the quote page.</p>

        <h2>Contact</h2>
        <p>Questions about this policy? Email <a href="mailto:{{ config('site.email') }}">info@customboxesexperts.com</a>,
        call <a href="tel:{{ config('site.phone_e164') }}">(888) 716-1078</a>, or write to Custom Boxes Experts, 1227 Solano Ave #9,
        Albany, CA 94706, USA.</p>
@endsection
