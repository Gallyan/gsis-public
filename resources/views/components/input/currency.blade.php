@php
$currencies = [['EUR', 'USD', 'GBP', 'CHF','BTC'],[
    'AFN', 'ALL', 'DZD', 'AOA', 'XCD', 'AAD', 'ARS', 'AMD', 'AWG', 'AUD', 'AZN', 'BSD', 'BHD', 'BDT', 'BBD', 'BYN', 'BZD', 'XOF', 'BMD', 'BTN', 'BOB', 'BAM', 'BWP', 'NOK', 'BRL', 'BND', 'BGN', 'BIF', 'KHR', 'XAF', 'CAD', 'CVE', 'KYD', 'CLP', 'CNY', 'COP', 'KMF', 'NZD', 'CRC', 'HRK', 'CUP', 'ANG', 'CZK', 'CDF', 'DKK', 'DJF', 'DOP', 'EGP', 'ERN', 'ETB', 'FKP', 'FJD', 'XPF', 'GMD', 'GEL', 'GHS', 'GIP', 'GTQ', 'GNF', 'GYD', 'HTG', 'HNL', 'HKD', 'HUF', 'ISK', 'INR', 'IDR', 'IRR', 'IQD', 'ILS', 'JMD', 'JPY', 'JOD', 'KZT', 'KES', 'KWD', 'KGS', 'LAK', 'LBP', 'LSL', 'LRD', 'LYD', 'MOP', 'MKD', 'MGA', 'MWK', 'MYR', 'MVR', 'MRO', 'MUR', 'MXN', 'MDL', 'MNT', 'MAD', 'MZN', 'MMK', 'NAD', 'NPR', 'NIO', 'NGN', 'KPW', 'OMR', 'PKR', 'PAB', 'PGK', 'PYG', 'PEN', 'PHP', 'PLN', 'QAR', 'RON', 'RUB', 'RWF', 'SHP', 'WST', 'STD', 'SAR', 'RSD', 'SCR', 'SLL', 'SGD', 'SBD', 'SOS', 'ZAR', 'KRW', 'SSP', 'LKR', 'SDG', 'SRD', 'SZL', 'SEK', 'SYP', 'TWD', 'TJS', 'TZS', 'THB', 'TOP', 'TTD', 'TND', 'TRY', 'TMT', 'UGX', 'UAH', 'AED', 'UYU', 'UZS', 'VUV', 'VEF', 'VND', 'YER', 'ZMW', 'ZWL'],
];
@endphp

<div class="flex">
    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-400 sm:text-sm">
        <x-icon.currencies />
    </span>

  <select {{ $attributes->merge(['class' => 'form-select block pl-3 pr-10 py-2 text-base leading-6 border-gray-300 text-gray-700 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5 rounded-l-none']) }}>
      <option value="">{{ __('Select currency') }}</option>

      @foreach($currencies as $group)
      <optgroup label="&boxh;&boxh;&boxh;&boxh;&boxh;&boxh;&boxh;&boxh;&boxh;&boxh;">

        @foreach($group as $code)
        <option value="{{ $code }}">{{ $code }} / {{ Lang::has('currencies.symbol-'.$code) ? __('currencies.symbol-'.$code) : __($code) }} / {{ Lang::has('currencies.name-'.$code) ? __('currencies.name-'.$code) : ''}}</option>
        @endforeach

        </optgroup>
      @endforeach

  </select>

</div>
