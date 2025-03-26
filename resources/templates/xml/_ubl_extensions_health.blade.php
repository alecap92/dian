<ext:UBLExtensions>
    <ext:UBLExtension>
        <ext:ExtensionContent>
            <CustomTagGeneral>
                <Name>Responsable</Name>
                <Value>url www.minsalud.gov.co</Value>
                <Name>Tipo, identificador:año del acto administrativo</Name>
                <Value>Resolución 506:2021</Value>
                <Interoperabilidad>
                    <Group schemeName="Sector Salud">
                        {{-- Health Users  --}}
                        @include('xml._health_users', ['healthUsers' => $healthfields->users_info])
                    </Group>
                </Interoperabilidad>
            </CustomTagGeneral>
        </ext:ExtensionContent>
    </ext:UBLExtension>
    <ext:UBLExtension>
        <ext:ExtensionContent>
            <sts:DianExtensions>
                @isset($resolution)
                    @if (preg_replace("/[\r\n|\n|\r]+/", "", $resolution->type_document_id) === '1' || preg_replace("/[\r\n|\n|\r]+/", "", $resolution->type_document_id) === '2' || preg_replace("/[\r\n|\n|\r]+/", "", $resolution->type_document_id) === '3')
                        @includeWhen($resolution->resolution, 'xml._invoice_control')
                    @endif
                @endisset
                <sts:InvoiceSource>
                    <cbc:IdentificationCode listAgencyID="6" listAgencyName="United Nations Economic Commission for Europe" listSchemeURI="urn:oasis:names:specification:ubl:codelist:gc:CountryIdentificationCode-2.1">{{preg_replace("/[\r\n|\n|\r]+/", "", $company->country->code)}}</cbc:IdentificationCode>
                </sts:InvoiceSource>
                <sts:SoftwareProvider>
                    <sts:ProviderID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" @if (preg_replace("/[\r\n|\n|\r]+/", "", $company->type_document_identification_id) === '6' || preg_replace("/[\r\n|\n|\r]+/", "", $company->type_document_identification_id) === '3') schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->dv)}}" @endif @if (preg_replace("/[\r\n|\n|\r]+/", "", $company->type_document_identification_id) === '6' || preg_replace("/[\r\n|\n|\r]+/", "", $company->type_document_identification_id) === '3') schemeName="31" @else schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_document_identification->code)}}" @endif >{{preg_replace("/[\r\n|\n|\r]+/", "", $company->identification_number)}}</sts:ProviderID>
                    <sts:SoftwareID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">{{preg_replace("/[\r\n|\n|\r]+/", "", $company->software->identifier)}}</sts:SoftwareID>
                </sts:SoftwareProvider>
                <sts:SoftwareSecurityCode schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"/>
                <sts:AuthorizationProvider>
                    <sts:AuthorizationProviderID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="4" schemeName="31">800197268</sts:AuthorizationProviderID>
                </sts:AuthorizationProvider>
                @if(isset($QRCode))
                    <sts:QRCode>{{$QRCode}}</sts:QRCode>
                @endif
            </sts:DianExtensions>
        </ext:ExtensionContent>
    </ext:UBLExtension>
    <ext:UBLExtension>
        <ext:ExtensionContent/>
    </ext:UBLExtension>
</ext:UBLExtensions>
