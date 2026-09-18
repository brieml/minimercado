@props(['valor'])
<span {{ $attributes }}>${{ number_format((float) $valor, 0, ',', '.') }}</span>
