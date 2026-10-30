@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" class="brand" style="display: inline-block;">{!! $slot !!}<span class="brand-dot">.</span></a>
</td>
</tr>
