{{--
    The certifying paragraph - the part that differs between a clearance and a
    certificate of indigency. Everything else on the paper (letterhead, request
    clause, signature) is identical, so it lives in certificate.blade.php.

    Shares the parent view's variables, but they are passed explicitly so the
    partial reads as self-contained and a refactor cannot silently blank it.
--}}
@switch($document->doc_type)
    @case('barangay_clearance')
        <p>
            That he/she is of good moral character and has no derogatory record, pending
            case or unpaid obligation on file in this Office, to the best of our knowledge
            and belief.
        </p>
        @break

    @case('certificate_of_residency')
        <p>
            That he/she has been residing in this barangay continuously and is a bona fide
            resident of Barangay {{ $barangayName }}, as shown by the household records kept
            by this Office.
        </p>
        @break

    @case('certificate_of_indigency')
        <p>
            That he/she belongs to an indigent family of this barangay and has no adequate
            means of livelihood to support the purpose stated below.
        </p>
        @break

    @case('certificate_of_good_moral')
        <p>
            That he/she is known to be of good moral character and has not been involved in
            any derogatory record or criminal case while residing in this community.
        </p>
        @break

    @case('certificate_of_no_income')
        <p>
            That he/she is not gainfully employed and has no verifiable source of income as
            of the date of issuance of this certification.
        </p>
        @break

    @case('certificate_of_live_birth')
        <p>
            That the person named above is a bona fide resident of this barangay, and that
            the civil registry record being supported by this certification is consistent
            with the household records available in this Office.
        </p>
        @break

    @case('business_permit_endorsement')
        <p>
            That he/she is a bona fide resident of this barangay and is of good standing in
            this community. This Office hereby endorses the application of the above-named
            person for a business permit to the City Government of {{ $cityName }}.
        </p>
        @break

    @case('barangay_id_application')
        <p>
            That he/she is a bona fide resident of this barangay and is applying for a
            Barangay Identification Card to be issued by this Office.
        </p>
        @break

    @case('certificate_of_solo_parent')
        <p>
            That he/she is a solo parent within the meaning of Republic Act No. 11861, as
            amended, and is a bona fide resident of this barangay.
        </p>
        @break

    @default
        <p>
            That he/she is a bona fide resident of this barangay, and that the document
            requested is supported by the records available in this Office.
        </p>
@endswitch
