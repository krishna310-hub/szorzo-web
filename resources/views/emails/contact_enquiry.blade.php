<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Enquiry – SZORZO AI</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=DM+Sans:wght@300;400;500&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #ffffff;
            font-family: 'DM Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper {
            max-width: 620px;
            margin: 48px auto;
            background-color: #ffffff;
            border: 1px solid #222;
            border-radius: 4px;
            overflow: hidden;
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #ffffff 0%, #ffffff 60%, #ffffff 100%);
            padding: 44px 48px 36px;
            border-bottom: 1px solid #2a0000;
            position: relative;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #cc0000 30%, #ff3333 50%, #cc0000 70%, transparent);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }

        .brand-dot {
            width: 8px;
            height: 8px;
            background: #cc0000;
            border-radius: 50%;
            box-shadow: 0 0 12px #cc0000;
        }

        .brand-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #000000;
        }

        .header-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 36px;
            font-weight: 800;
            color: rgb(0, 0, 0);
            line-height: 1.1;
            letter-spacing: -0.5px;
        }

        .header-title strong {
            color: #fff;
            font-weight: 600;
            display: block;
        }

        .header-sub {
            margin-top: 10px;
            font-size: 12px;
            color: #000000;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* ── Timestamp Badge ── */
        .timestamp-bar {
            background: #ffffff;
            padding: 12px 48px;
            border-bottom: 1px solid #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .timestamp-label {
            font-size: 10px;
            letter-spacing: 2px;
            color: #000000;
            text-transform: uppercase;
        }

        .timestamp-value {
            font-size: 11px;
            color: #000000;
            font-weight: 500;
        }

        .status-badge {
            background: rgba(204, 0, 0, 0.12);
            border: 1px solid rgba(204, 0, 0, 0.3);
            color: #ff4444;
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 2px;
        }

        /* ── Body ── */
        .body {
            padding: 40px 48px;
        }

        .section-label {
            font-size: 9px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #ffffff;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #1e1e1e;
        }

        /* ── Field Grid ── */
        .field-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            margin-bottom: 32px;
        }

        .field-item {
            padding: 16px 0;
            border-bottom: 1px solid #000000;
        }

        .field-item:nth-child(odd) {
            padding-right: 24px;
            border-right: 1px solid #000000;
        }

        .field-item:nth-child(even) {
            padding-left: 24px;
        }

        .field-item.full-width {
            grid-column: 1 / -1;
            padding-right: 0;
            border-right: none;
        }

        .field-label {
            font-size: 9px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #000000;
            margin-bottom: 6px;
        }

        .field-value {
            font-size: 14px;
            color: #000000;
            font-weight: 400;
            line-height: 1.4;
        }

        .field-value.name-val {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px;
            font-weight: 400;
            color: #000000;
        }

        .field-value.email-val {
            color: #cc4444;
        }

        /* ── Relationship Tag ── */
        .rel-tag {
            display: inline-block;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #2a2a2a;
            color: #aaa;
            font-size: 11px;
            letter-spacing: 1px;
            padding: 4px 12px;
            border-radius: 2px;
        }

        /* ── Message Block ── */
        .message-block {
            background: #0d0d0d;
            border: 1px solid #1e1e1e;
            border-left: 3px solid #cc0000;
            padding: 20px 24px;
            margin-top: 8px;
            border-radius: 0 2px 2px 0;
        }

        .message-block p {
            font-size: 13px;
            color: #000000;
            line-height: 1.8;
        }

        /* ── CTA ── */
        .cta-section {
            padding: 0 48px 40px;
        }

        .cta-btn {
            display: inline-block;
            background: #cc0000;
            color: #fff;
            text-decoration: none;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 14px 32px;
            border-radius: 2px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 500;
        }

        /* ── Footer ── */
        .footer {
            background: #ffffff;
            border-top: 1px solid #1a1a1a;
            padding: 24px 48px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-brand {
            font-family: 'Cormorant Garamond', serif;
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #000000;
        }

        .footer-note {
            font-size: 10px;
            color: #000000;
            letter-spacing: 1px;
        }

        /* ── Divider ── */
        .red-rule {
            height: 1px;
            background: linear-gradient(90deg, #cc0000, transparent);
            margin: 0 48px 32px;
            opacity: 0.4;
        }
    </style>
</head>

<body>

    <div class="wrapper">

        {{-- Header --}}
        <div class="header">
            <div class="brand">
                <div class="brand-dot"></div>
                <span class="brand-name">SZORZO</span>
            </div>
            <div class="header-title">
                New Contact
                <strong>Enquiry</strong>
            </div>
            <div class="header-sub">Inbound Lead Notification</div>
        </div>

        {{-- Timestamp Bar --}}
        <div class="timestamp-bar">
            <span class="timestamp-label">Received</span>
            <span class="timestamp-value">{{ now()->format('D, d M Y — H:i T') }}</span>
            <span class="status-badge">● New</span>
        </div>

        {{-- Body --}}
        <div class="body">
            <div class="section-label">Contact Details</div>

            <div class="field-grid">

                {{-- First Name --}}
                <div class="field-item">
                    <div class="field-label">First Name</div>
                    <div class="field-value name-val">{{ $data['firstname'] }}</div>
                </div>

                {{-- Last Name --}}
                <div class="field-item">
                    <div class="field-label">Last Name</div>
                    <div class="field-value name-val">{{ $data['lastname'] }}</div>
                </div>

                {{-- Email --}}
                <div class="field-item full-width">
                    <div class="field-label">Email Address</div>
                    <div class="field-value email-val">{{ $data['email'] }}</div>
                </div>

                {{-- Company --}}
                <div class="field-item">
                    <div class="field-label">Company</div>
                    <div class="field-value">{{ $data['company'] }}</div>
                </div>

                {{-- Phone --}}
                <div class="field-item">
                    <div class="field-label">Phone</div>
                    <div class="field-value">{{ $data['phone'] }}</div>
                </div>

                {{-- Relationship --}}
                <div class="field-item full-width" style="border-bottom:none;">
                    <div class="field-label">Relationship with SZORZO</div>
                    <div class="field-value">
                        <span class="rel-tag">{{ $data['relationship'] }}</span>
                    </div>
                </div>

            </div>

            {{-- Message --}}
            @if (!empty($data['info']))
                <div class="section-label">Message</div>
                <div class="message-block">
                    <p>{{ $data['info'] }}</p>
                </div>
            @endif

        </div>

        <div class="red-rule"></div>

        {{-- CTA --}}
        <div class="cta-section">
            <a href="mailto:{{ $data['email'] }}" class="cta-btn">Reply to Enquiry →</a>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <span class="footer-brand">SZORZO AI</span>
            <span class="footer-note">Automated notification — do not reply directly</span>
        </div>

    </div>

</body>

</html>
