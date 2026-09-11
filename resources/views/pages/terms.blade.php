@extends('layouts.legal')

@section('title', 'Terms & Conditions | Custom Boxes Experts')
@section('description', 'Terms governing quotes, artwork approval, production, shipping and returns for custom rigid box orders from Custom Boxes Experts.')
@section('heading', 'Terms &amp; Conditions')
@section('updated', 'Last updated: 4 August 2026. These terms apply to quotes issued and orders accepted by Custom Boxes Experts, 1227 Solano Ave #9, Albany, CA 94706, USA.')

@section('footer-links')
<a href="{{ route('home') }}">Rigid boxes</a> &middot; <a href="{{ route('privacy') }}">Privacy Policy</a>
@endsection

@section('legal')
    <h2>1. Quotes and pricing</h2>
        <ul>
          <li>Price ranges shown on our website are indicative estimates for budgeting, not offers. Only a written quote issued by our team is binding.</li>
          <li>Written quotes are valid for 15 days unless stated otherwise, and assume the specifications supplied at the time of quoting.</li>
          <li>Changes to size, board thickness, stock, finishing, quantity or insert after quoting will change the price. We will re-quote before proceeding.</li>
        </ul>

        <h2>2. Artwork and approval</h2>
        <ul>
          <li>Supply print-ready vector artwork (AI, EPS or PDF) at 300dpi, in CMYK or with Pantone values, laid out on the dieline we provide. We can place your assets on the dieline at no charge if you do not have print-ready files.</li>
          <li>You are responsible for the accuracy of artwork content, spelling, barcodes and regulatory text, and you confirm you hold the rights to all logos and images supplied.</li>
          <li>Production begins only after you approve the digital proof and the pre-production sample in writing. Approval transfers responsibility for approved content to you.</li>
        </ul>

        <h2>3. Production, tolerances and turnaround</h2>
        <ul>
          <li>Standard turnaround is 12&ndash;15 business days from written approval of artwork and pre-production sample. Rush timelines, where offered, are confirmed in writing on the quote.</li>
          <li>Turnaround excludes shipping transit time and does not run during public holidays or delays caused by late approvals, artwork changes or payment.</li>
          <li>Dimensional tolerance on finished rigid boxes is &plusmn;1/8&Prime;. Printed color is matched as closely as commercially achievable; a small variation from screen or a prior run is normal in offset printing.</li>
          <li>Quantity delivered may vary by up to &plusmn;5% of the ordered quantity, which is standard in custom packaging manufacturing. You are invoiced for the quantity delivered.</li>
        </ul>

        <h2>4. Payment</h2>
        <ul>
          <li>New accounts pay in full before production unless credit terms have been agreed in writing.</li>
          <li>Accepted methods are listed on your invoice. All prices are in US dollars.</li>
          <li>Tooling, artwork and setup work already performed is chargeable if an order is cancelled after approval.</li>
        </ul>

        <h2>5. Shipping and delivery</h2>
        <ul>
          <li>Ground shipping to a single address in the contiguous United States or Canada is included in quoted prices unless stated otherwise. Expedited freight, split shipments, residential or liftgate delivery may be quoted separately.</li>
          <li>Delivery dates are estimates. We are not liable for carrier delays outside our control.</li>
          <li>Inspect shipments on arrival and note any visible damage on the carrier&rsquo;s paperwork.</li>
        </ul>

        <h2>6. Claims and returns</h2>
        <ul>
          <li>Custom-manufactured packaging is made to your approved specification and cannot be returned for change of mind.</li>
          <li>Report manufacturing defects or shortages within 7 days of delivery, with photographs and your order number. Verified defects are remedied by reprint, replacement or credit at our discretion.</li>
          <li>Claims are limited to the value of the affected goods. We are not liable for indirect or consequential loss, including lost sales or missed launch dates.</li>
        </ul>

        <h2>7. Intellectual property</h2>
        <p>You retain ownership of your artwork and trademarks. You grant us a limited license to reproduce them for the
        purpose of producing your order, and to show finished work in our portfolio unless you ask us in writing not to.
        Dielines and structural drawings we create remain our property.</p>

        <h2>8. Marketing communications</h2>
        <p>Contacting us for a quote means we may reach you by phone, email or SMS about that inquiry, as described in our
        <a href="{{ route('privacy') }}">Privacy Policy</a>. You can opt out at any time.</p>

        <h2>9. Governing law</h2>
        <p>These terms are governed by the laws of the State of California, and the courts of Alameda County, California
        have exclusive jurisdiction over any dispute.</p>

        <h2>10. Contact</h2>
        <p>Custom Boxes Experts, 1227 Solano Ave #9, Albany, CA 94706, USA.
        <a href="tel:{{ config('site.phone_e164') }}">(888) 716-1078</a> &middot;
        <a href="mailto:{{ config('site.email') }}">info@customboxesexperts.com</a></p>
@endsection
