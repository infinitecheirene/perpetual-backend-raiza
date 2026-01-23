<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    {{-- title --}}
    <title>Certificate of Legitimacy - {{ $legitimacy->alias }}</title>

    {{-- font style --}}
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 10mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* Waltermark */
        .watermark-logo .left,
        .watermark-logo .right {
            display: table-cell;
            width: 50%;
            vertical-align: middle;
            text-align: center;
        }

        /* Certificate Container */
        .certificate-container {
            border: 10px double #800000;
            padding: 10px;
            margin: 15px 20px;
            /* height: 100%; */
            /* position: relative; */
        }

        /* Border */
        .inner-border {
            border: 3px solid #d4af37;
            padding: 30px 40px;
            /* height: 100%; */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }


        /* Watermark Background - ENLARGED */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 60%;
            z-index: -1;
            opacity: 0.08;

            display: table;
            /* DomPDF-safe horizontal layout */
        }

        .watermark-logo {
            display: table-cell;
            width: 100%;
            height: 100%;
            vertical-align: middle;
            text-align: center;
        }

        .watermark-logo img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        /* Border */
        .inner-border {
            display: block;
            border: 3px solid #d4af37;
            /* height: 100%; */
            /* display: flex; */
            /* flex-direction: column; */
            justify-content: flex-start;
            /* override space-between */
        }


        /* Header with Dual Logos */
        .header-section {
            text-align: center;
            padding-bottom: 5px;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .logo-left,
        .logo-right {
            display: table-cell;
            width: 25%;
            vertical-align: middle;
            text-align: center;
        }

        .header-text {
            display: table-cell;
            width: 50%;
            vertical-align: middle;
            text-align: center;
        }

        .logo {
            width: 180px;
            height: 180px;
            border-radius: 100%;
        }

        .logo-placeholder {
            width: 80px;
            height: 80px;
            display: inline-block;
            border-radius: 50%;
        }

        .organization-name {
            font-size: 22px;
            font-weight: bold;
            color: #800000;
            text-transform: uppercase;
            font-family: cursive;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .organization-subtitle {
            font-size: 20px;
            color: #800000;
            text-transform: uppercase;
            margin: 2px 0;
        }

        /* Certificate Title */
        .certificate-title {
            text-align: center;
            margin: 20px 0 15px 0;
        }

        .certificate-title h1 {
            font-size: 44px;
            font-weight: bold;
            color: #800000;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 8px;
            font-family: cursive;
            text-align: center;
        }

        .certificate-subtitle {
            font-size: 12px;
            color: #333;
            font-style: italic;
            margin-bottom: 5px;
        }

        /* Main Content */
        .content-section {
            text-align: center;
            flex-grow: 1;
        }


        .recipient-name {
            font-family: 'Great Vibes', cursive;
            font-size: 36px;
            text-decoration: underline;
            text-align: center;


            color: #000000;
            margin: 15px 0;
            text-transform: uppercase;
            display: inline-block;
            padding-bottom: 5px;
        }

        .alias-display {
            font-size: 13px;
            font-style: italic;
            color: #851003;
            /* margin: 10px 0 2px 0; */
        }

        /* Details Table */
        .details-section {
            /* margin: 20px auto; */
            /* max-width: 500px; */
        }

        .detail-row {
            display: table;
            width: 100%;
            margin: 8px 0;
            font-size: 11px;
        }

        .detail-label {
            display: table-cell;
            width: 45%;
            text-align: right;
            padding-right: 15px;
            font-weight: bold;
            color: #800000;
        }

        .detail-value {
            display: table-cell;
            width: 55%;
            text-align: left;
            color: #333;
        }

        /* Statement */
        .certificate-statement {
            /* margin: 20px 50px; */
            text-align: justify;
            font-size: 10px;
            line-height: 1.6;
            color: #333;
        }

        /* === Signature Styling (Image-based style) === */
        .signatories-section {
            position: relative;
            width: 100%;
            margin: 30px auto 10px auto;
            display: table;
            /* KEY */
            table-layout: fixed;
            text-align: center;
            clear: both;
            font-size: 8px;
            width: 85%;
        }

        /* Clear floats properly so footer follows signatures */
        .signatories-section::after {
            content: "";
            display: table;
            clear: both;
        }

        .signatory-left,
        .signatory-right {
            width: 45%;
        }

        .signatory-cell {
            display: table-cell;
            width: 100%;
            vertical-align: bottom;
            text-align: center;
        }

        .signatory-left {
            float: left;
            width: 45%;
            text-align: center;
            padding-left: 0;
            /* REMOVE OFFSET */
        }

        .signatory-right {
            float: right;
            width: 45%;
            text-align: center;
            padding-right: 0;
            /* REMOVE OFFSET */
        }

       
        .signatory-box {
            text-align: center;
            /* margin-bottom: 10px; */
        }

        .signature-image {
            display: block;
            max-height: 85px;
            max-width: 120px;
            margin: 0 auto 6px auto;
        }

        .signatory-name {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 120px;
            margin: 4px auto;
        }

        .signatory-left .signature-line {
            justify-content: center;
            width: 40px;
            margin-left: 0;
        }

        .signatory-right .signature-line {
            margin-left: auto;
            width: 40px;
            margin: 2px 0 2px 0;
            justify-content: center;
        }

        .signatory-bottom .signature-line {
            margin-left: auto;
            justify-content: center;
            width: 40px;
            margin-right: auto;
        }

        .signatory-role {
            font-size: 12px;
            font-style: italic;
            text-align: center;
        }


        .signatory-date {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }

        /* Footer */
        .certificate-footer {
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 3px solid #800000;
            padding-top: 10px;
            margin-top: 15px;
        }

        .certificate-number {
            font-weight: bold;
            color: #1a5490;
            font-size: 9px;
        }

        .footer-line {
            margin-top: 3px;
        }
    </style>
</head>

<body>

    <div class="watermark">
        <div class="watermark-logo left">
            <img src="{{ public_path('images/perpetuallogo.jpg') }}" alt="Watermark">
        </div>

        <div class="watermark-logo right">
            <img src="{{ public_path('images/perpetuallogo.jpg') }}" alt="Watermark">
        </div>
    </div>




    <div class="certificate-container">
        <div class="inner-border">

            <!-- Header with Dual Logos -->
            <div class="header-section">
                <div class="header-content">
                    <!-- Left Logo -->
                    <div class="logo-left">
                        @if(file_exists(public_path('images/perpetuallogo.jpg')))
                            <img src="{{ public_path('images/perpetuallogo.jpg') }}" alt="Logo" class="logo">
                        @else
                            <div class="logo-placeholder"></div>
                        @endif
                    </div>

                    <!-- Center Text -->
                    <div class="header-text">
                        <div class="organization-name">{{ strtoupper($legitimacy->alias) }}</div>
                        <div class="organization-subtitle">{{ $legitimacy->chapter }}</div>
                    </div>

                    <!-- Right Logo -->
                    <div class="logo-right">
                        @if(file_exists(public_path('images/perpetuallogo.jpg')))
                            <img src="{{ public_path('images/perpetuallogo.jpg') }}" alt="Logo" class="logo">
                        @else
                            <div class="logo-placeholder"></div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="content-section">
                <!-- Title -->
                <div class="certificate-title">
                    <h1>Certificate of Appreciation</h1>
                    <div class="certificate-subtitle">This certificate is presented to:</div>
                </div>


                <div class="recipient-name">{{ strtolower($user->name) }}</div>
                <div class="alias-display">
                    Given this {{ \Carbon\Carbon::parse($certificateDate)->format('j') }} day of
                    {{ \Carbon\Carbon::parse($certificateDate)->format('F') }},
                    {{ \Carbon\Carbon::parse($certificateDate)->format('Y') }} at University of Perpetual Help.
                    This certificate serves as official documentation of membership standing and affirms that the bearer
                    has met all requirements and obligations as prescribed by the Triskelion Grand Fraternity. The
                    holder of this certificate is recognized as a brother/sister in good standing and is entitled to all
                    rights and privileges thereof.
                </div>


                <!-- Details -->
                <div class="details-section">
                    {{-- <div class="detail-row">
                        <div class="detail-label">Chapter:</div>
                        <div class="detail-value">{{ $legitimacy->chapter }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Position:</div>
                        <div class="detail-value">{{ $legitimacy->position }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Fraternity Number:</div>
                        <div class="detail-value">{{ $legitimacy->fraternity_number }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Certificate Date:</div>
                        <div class="detail-value">{{ $certificateDate }}</div>
                    </div> --}}
                </div>

                <!-- Statement -->
                {{-- <div class="certificate-statement">
                    @if($legitimacy->admin_note)
                    {{ $legitimacy->admin_note }}
                    @else
                    This certificate serves as official documentation of membership standing and affirms that the bearer
                    has met all requirements and obligations as prescribed by the Triskelion Grand Fraternity. The
                    holder of this certificate is recognized as a brother/sister in good standing and is entitled to all
                    rights and privileges thereof.
                    @endif
                </div> --}}

                <!-- Statement -->
                {{-- <div class="certificate-statement">
                    @if($legitimacy->admin_note)
                    {{ $legitimacy->admin_note }}
                    @endif
                </div> --}}

                <!-- Signatories - Left/Right Float Layout -->
                @if($signatories && count($signatories) > 0)
                    <div class="signatories-section">

                        {{-- LEFT --}}
                        @if(isset($signatories[0]))
                            <div class="signatory-cell">
                                <div class="signatory-box">
                                    @if($signatories[0]->signature_url && file_exists(public_path($signatories[0]->signature_url)))
                                        <img src="{{ public_path($signatories[0]->signature_url) }}" class="signature-image">
                                    @endif
                                    <div class="signature-line"></div>
                                    <div class="signatory-name">{{ strtoupper($signatories[0]->name) }}</div>
                                    <div class="signatory-role">{{ $signatories[0]->role }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- RIGHT --}}
                        @if(isset($signatories[1]))
                            <div class="signatory-cell">
                                <div class="signatory-box">
                                    @if($signatories[2]->signature_url && file_exists(public_path($signatories[2]->signature_url)))
                                        <img src="{{ public_path($signatories[2]->signature_url) }}" class="signature-image">
                                    @endif
                                    <div class="signature-line"></div>
                                    <div class="signatory-name">{{ strtoupper($signatories[2]->name) }}</div>
                                    <div class="signatory-role">{{ $signatories[2]->role }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- BOTTOM CENTER --}}
                        @if(isset($signatories[2]))
                            <div class="signatory-cell">
                                <div class="signatory-box">
                                    @if($signatories[1]->signature_url && file_exists(public_path($signatories[1]->signature_url)))
                                        <img src="{{ public_path($signatories[1]->signature_url) }}" class="signature-image">
                                    @endif
                                    <div class="signature-line"></div>
                                    <div class="signatory-name">{{ strtoupper($signatories[1]->name) }}</div>
                                    <div class="signatory-role">{{ $signatories[1]->role }}</div>
                                </div>
                            </div>
                        @endif

                    </div>
                @endif

            </div>

            <!-- Footer -->
            <div class="certificate-footer">
                {{-- <div class="certificate-number"></div> --}}
                <div class="footer-line">Issued on {{ $generatedDate }}</div>
                <div class="footer-line">© {{ date('Y') }} Triskelion Grand Fraternity. All rights reserved.</div>
            </div>
        </div>
    </div>
</body>

</html>