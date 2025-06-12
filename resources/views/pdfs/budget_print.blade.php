<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>#{{ $budget->id }} | {{ $budget->title }} - {{ $budget->client_name }}</title>
    <style>
        /* Define your base styles */
        body {
            font-family: arial, DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10px;
            position: relative; /* Essential for positioning the watermark */
        }

        /* Watermark Styles */
        .watermark {
            position: fixed; /* Fixes the watermark relative to the viewport/page */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg); /* Center and rotate for a diagonal effect */
            opacity: 0.1; /* Adjust transparency as needed (0.1 to 0.3 usually works well) */
            font-size: 80px; /* Adjust font size for text watermark */
            color: #cccccc; /* Light gray color for text watermark */
            white-space: nowrap; /* Prevent text watermark from wrapping */
            pointer-events: none; /* Allows text/elements behind the watermark to be selectable/clickable */
            z-index: -1; /* Ensures the watermark stays behind your content */
        }

        /* If you want an image watermark */
        .watermark-image {
            position: fixed;
            top: 45%;
            left: 45%;
            transform: translate(-50%, -50%); /* Center the image */
            opacity: 0.1; /* Adjust transparency */
            width: 60%; /* Adjust image width as needed (percentage of page width) */
            height: auto;
            pointer-events: none;
            z-index: -1;
        }

        /* If you want the watermark to cover the entire page */
        .watermark-full-page {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('{{ asset("path/to/your/watermark.png") }}'); /* Replace with your image path */
            background-repeat: repeat; /* or no-repeat, space, round */
            background-position: center center;
            background-size: cover; /* or contain, or specific dimensions like 200px 200px */
            opacity: 0.05; /* Very low opacity for full-page background */
            pointer-events: none;
            z-index: -1;
        }

        .container {
            width: 100%;
            box-sizing: border-box;
            font-family: arial;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            padding: 2px 4px; /* Adjust padding for cells */
            vertical-align: middle;
        }
        .header-table, .client-info-table, .items-table, .summary-table,
        .responsibilities-table, .other-info-table {
            margin-bottom: 10px; /* Space between sections */
            border: 1px solid #000; /* Add borders as per PDF */
        }
        .items-header th {
            text-align: center; /* Center text for all th elements in the header */
        }
        .header-table td, .client-info-table td, .items-table td, .summary-table td,
        .responsibilities-table td, .other-info-table td {
            border: 1px solid #000;
            vertical-align:top;
        }

        .items-table th {
            background-color: #cccccc;
            font-size: 9px;
            padding: 1px;
            /* text-align is now handled by .items-header th */
        }

        /* Specific styles for sections */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .border-bottom { border-bottom: 1px solid #000; }
        .valmar-logo {
            width: 80px; /* Adjust logo size */
            height: auto;
            display: block; /* Make the image a block-level element */
            margin: 0 auto; /* This property centers a block-level element if it has a defined width */
        }
        .company-info {
            font-size: 17px;
            line-height: 20px;
            text-align: center;
            font-weight: bold;
            font-family: arial;
        }
        .company-info-data {
            font-size: 15px;
            color: #444
        }
        .company-info a {
            color: #444;
        }
        .budget-header td {
            font-weight: bold;
            font-size: 12px;
            font-weight: thin;
            text-align: center;
            padding:1px;
        }
        .budget-header td span {
            font-weight: normal;
        }
        .client-header td {
             background-color: #cccccc;
             padding: 1px;
             font-weight: bold;
             text-align: center;
        }
        .section-title {
            background-color: #cccccc; /* Light gray background for titles */
            font-weight: bold;
            padding: 1px;
            text-align: center;
            border: 1px solid #000;
        }
        .summary-label {
            font-weight: bold;
            text-align: left;
        }
        .summary-value {
            font-weight: bold;
            text-align: right;
            padding-right: 5px;
        }
        .total-row {
            background-color: #cccccc;
        }
        .responsibilities-table th {
            background-color: #cccccc;
            font-weight: bold;
            text-align: center;
        }
        .responsibility-text {
            padding: 5px;
            font-size: 10px;
        }
        .other-info-table th {
            background-color: #ccc;
            font-weight: bold;
            text-align: center;
        }
        .other-info-table th.prazo {
            background-color: #eee;
        }
        .other-info-table td {
            padding: 0px 5px;
            font-size: 10px;
        }
        .footer-info {
            width:100%;
            position:fixed;
            bottom:0px;
            text-align: center;
            font-size: 15px;
            color: #222;
            font-family: arial;
            font-weight: bold;
            margin-top: 20px;
        }

        .footer-info a{
            color: #222;
            text-decoration:none;
        }
    </style>
</head>
<body>
<img src="{{ asset('storage/img/theme/logos/complete_logo.svg') }}" alt="Watermark" class="watermark-image">
    <div class="'container'">
        <table class="header-table">
            <tr>
            <td style="width: 20%; text-align: center; background-color:#ccc; vertical-align: middle;">
                <img src="{{ asset('storage/img/theme/logos/complete_logo.svg') }}" alt="VALMAR Logo" class="valmar-logo">
            </td>
                <td style="width: 80%; text-align: center;">
                    <div class="company-info">
                        <span class="font-bold">VALMAR | Nascimento Correia</span><br>
                        <span class="font-bold">Serviços de Manutenção Industrial e Isolamento Térmico Ltda-Me</span><br>
                        <span class="company-info-data">
                        CNPJ: 15.544.278/0001-05<br>
                        Email: <a href="mailto:valmarmetalurgica@outlook.com.br">valmarmetalurgica@outlook.com.br</a>
    </span>
                    </div>
                </td>
            </tr>
        </table>

        <table class="header-table">
            <tr class="budget-header">
                <td style="width: 33%;">Orçamento nº: <span>{{ $budget->id }}</span></td>
                <td style="width: 34%;">Emitido em: <span>{{ \Carbon\Carbon::parse($budget->budget_date)->format('d/m/Y') }}</span></td>
                <td style="width: 33%;">Validade: <span>{{ $budget->validity }} dias</span></td>
            </tr>
        </table>

        <table class="client-info-table">
            <thead>
                <tr class="client-header">
                    <td colspan="8">DADOS DO CLIENTE</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 10%;font-weight:bold; background-color:#ccc;text-align:center;">NOME</td>
                    <td colspan="7">{{ $budget->client_name }}</td>
                </tr>
                <tr>
                    <td style="width: 10%;font-weight:bold; background-color:#ccc;text-align:center;">TELEFONE</td>
                    <td style="width: 15%;">{{ $budget->client_phone }}</td>
                    <td style="width: 7%;font-weight:bold; background-color:#ccc;text-align:center;">E-MAIL</td>
                    <td style="width: 28%;">{{ $budget->client_email }}</td>
                    <td style="width: 7%;font-weight:bold; background-color:#ccc;text-align:center;">CPF/CNPJ</td>
                    <td style="width: 17%;">{{ $budget->client_cpf_cnpj }}</td>
                    <td style="width: 3%;font-weight:bold; background-color:#ccc;text-align:center;">CEP</td>
                    <td style="width: 1s%;">{{ $budget->client_cep }}</td>
                </tr>
            </tbody>
        </table>

        <table class="items-table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th colspan="3" style="text-align:center; font-size:12px; border: 1px solid #000">{{ $budget->title }}</th>
                </tr>
                <tr class="items-header">
                    <th style="width: 1%; white-space: nowrap; text-align: center;">#</th>
                    <th style="text-align: left;">DESCRIÇÃO</th>
                    <th style="width: 1%; white-space: nowrap; text-align: center;">VALOR</th>
                </tr>
            </thead>
            <tbody>
                @foreach($budget->items as $index => $item)
                <tr>
                    <td style="white-space: nowrap; text-align:center; vertical-align:top; background-color:#eee; padding:5px 7px;">{{ sprintf('%02d', $index + 1) }}</td>
                    <td stype="vertical-align:top; padding:5px 7px;">{{ $item->item_name }}</td>
                    <td style="white-space: nowrap; text-align:center; vertical-align:top;  padding:5px 7px;">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>


        <table class="summary-table">
            <tr>
                <td style="width: 25%; padding:0px; text-align:center; vertical-align: center; font-size:15px; line-height:5px;">
                    <p>SUBTOTAL</p>
                    <p style="font-weight:bold; font-family:arial">R$ {{ number_format($budget->total_value + $budget->discount_amount - $budget->additional_amount, 2, ',', '.') }}</p>
                </td>
                <td style="width: 25%; padding:0px; text-align:center; vertical-align: center; font-size:15px; line-height:5px;">
                    <p>DESCONTO</p>
                    <p style="font-weight:bold; font-family:arial">R$ {{ number_format($budget->discount_amount, 2, ',', '.') }}</p>
                </td>
                <td style="width: 25%; padding:0px; text-align:center; vertical-align: center; font-size:15px; line-height:5px;">
                    <p>ACRÉSCIMO</p>
                    <p style="font-weight:bold; font-family:arial">R$ {{ number_format($budget->additional_amount, 2, ',', '.') }}</p>
                </td>
                <td style="width: 25%; padding:0px; text-align:center; vertical-align: center; font-size:15px; line-height:5px;">
                    <p>TOTAL</p>
                    <p style="font-weight:bold; font-family:arial">R$ {{ number_format($budget->total_value, 2, ',', '.') }}</p>
                </td>
            </tr>
        </table>


        <table class="responsibilities-table">
            <thead>
                <tr>
                    <th colspan="2" class="section-title">RESPONSABILIDADES</th>
                </tr>
                <tr>
                    <th style="width: 50%;">DA CONTRATADA</th>
                    <th style="width: 50%;">DO CONTRATANTE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="responsibility-text">{{ $budget->contracted_responsibility }}</td>
                    <td class="responsibility-text">{{ $budget->contractor_responsibility }}</td>
                </tr>
            </tbody>
        </table>

        <table class="other-info-table">
            <thead>
            <tr>
                    <th colspan="2" class="section-title">OUTRAS INFORMAÇÕES</th>
                </tr>
                <tr>
                    <th style="width: 50%;">DO SERVIÇO</th>
                    <th style="width: 50%;">DO PAGAMENTO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 50%;">    
                        <p>{{ $budget->description }}</p>
                    </td>
                    <td style="width: 50%;">
                        <p style="font-weight:bold">FORMA DE PAGAMENTO:</p>
                        <p style="white-space: pre-wrap;">{{ $budget->payment_method_description }}</p>
                        <p style="font-weight:bold">INFORMAÇÕES BANCÁRIAS:</p>
                        <p style="white-space: pre-wrap;">{{ $budget->bank_info_description }}</p>
                    </td>
                </tr>
                <tr class="prazo">
                    <td colspan="2" class="text-center font-bold" style="padding:2px; vertical-align:center;">
                        PRAZO: {{ $budget->deadline }} {{ $budget->deadline_type }} {{ $budget->deadline_start_description }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="footer-info">
            Telefones: <a href="tel:5573988558247">(73) 98855-8247</a> | <a href="tel:5573988559571">(73) 98855-9571</a> | <a href="tel:5573981121072">(73) 98112-1072</a><br>
            Av. JS Pinheiro, 2193 - Bairro Lomanto - CEP 45601-051
        </div>
    </div>
</body>
</html>