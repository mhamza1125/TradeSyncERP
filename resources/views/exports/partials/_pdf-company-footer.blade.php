{{--
    Shared footer for all PDF exports.
    Variables:
      $centerText — document-specific centre label (e.g. invoice number)
--}}
<div class="pdf-footer">
    <table>
        <tr>
            <td class="pf-addr">123 Business Street, City, Country</td>
            <td class="pf-center">{{ $centerText ?? '' }}</td>
            <td class="pf-right"></td>
        </tr>
        <tr>
            <td class="pf-contact">+00 000 0000000 &nbsp;|&nbsp; info@example.com &nbsp;|&nbsp; www.example.com</td>
            <td class="pf-center"></td>
            <td></td>
        </tr>
    </table>
</div>
