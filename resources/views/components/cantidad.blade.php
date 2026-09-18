@props(['valor'])
<span {{ $attributes }}>{{ rtrim(rtrim(number_format((float) $valor, 3, ',', '.'), '0'), ',') }}</span>
