<cac:{{$node}}>
    <cbc:AdditionalAccountID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_organization->code)}}</cbc:AdditionalAccountID>
    <cac:Party>
        @if(isset($request['actividadeconomica']))
            <cbc:IndustryClassificationCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $request['actividadeconomica'])}}</cbc:IndustryClassificationCode>
        @endif
        @if ($user->company->type_organization->code == 2)
            <cac:PartyIdentification>
               <cbc:ID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:ID>
            </cac:PartyIdentification>
        @endif
        <cac:PartyName>
            <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->name)}}</cbc:Name>
        </cac:PartyName>
            <cac:PhysicalLocation>
                @if(!isset($supplier) &&  $typeDocument->id == '24')
                    <cbc:LocationTypeCode listID="01">{{preg_replace("/[\r\n|\n|\r]+/", "", $request['stratum_id'])}}</cbc:LocationTypeCode>
                @endif
                <cac:Address>
                    @isset($supplier)
                        <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->code)}}</cbc:ID>
                        <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->name))}}</cbc:CityName>
                        @if(isset($user->postal_zone_code))
                            <cbc:PostalZone>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->postal_zone_code)}}</cbc:PostalZone>
                        @endif
                        @if($typeDocument->id == 15)
                            <cbc:PostalZone>{{preg_replace("/[\r\n|\n|\r]+/", "", $request['postal_zone_code'])}}</cbc:PostalZone>
                        @endif
                        <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->department->name))}}</cbc:CountrySubentity>
                        <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->department->code)}}</cbc:CountrySubentityCode>
                    @else
                        @if($user->company->country->id == 46)
                            <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->code)}}</cbc:ID>
                            <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->name))}}</cbc:CityName>
                            @if(isset($user->postal_zone_code))
                                <cbc:PostalZone>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->postal_zone_code)}}</cbc:PostalZone>
                            @endif
                            <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->department->name))}}</cbc:CountrySubentity>
                            <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->department->code)}}</cbc:CountrySubentityCode>
                        @else
                            <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", "00001")}}</cbc:ID>
                            <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality_name))}}</cbc:CityName>
                            @if(isset($user->postal_zone_code))
                                <cbc:PostalZone>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->postal_zone_code)}}</cbc:PostalZone>
                            @endif
                            <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->state_name))}}</cbc:CountrySubentity>
                            <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", "01")}}</cbc:CountrySubentityCode>
                        @endif
                    @endisset
                    <cac:AddressLine>
                        <cbc:Line>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->address)}}</cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->country->code)}}</cbc:IdentificationCode>
                        <cbc:Name languageID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->language->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->country->name)}}</cbc:Name>
                    </cac:Country>
                </cac:Address>
            </cac:PhysicalLocation>
        <cac:PartyTaxScheme>
            <cbc:RegistrationName>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->name)}}</cbc:RegistrationName>
            @if($typeDocument->id == '11' || $typeDocument->id == '13' || $typeDocument->id == '26'|| $typeDocument->id == '16')
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="31">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:CompanyID>
            @else
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:CompanyID>
            @endif
            <cbc:TaxLevelCode listName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_regime->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_liability->code)}}</cbc:TaxLevelCode>
            <cac:RegistrationAddress>
                @if($user->company->country->id == 46)
                    <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->code)}}</cbc:ID>
                    <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->name))}}</cbc:CityName>
                    <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->department->name))}}</cbc:CountrySubentity>
                    <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->department->code)}}</cbc:CountrySubentityCode>
                @else
                    <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", "00001")}}</cbc:ID>
                    <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality_name))}}</cbc:CityName>
                    <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->state_name))}}</cbc:CountrySubentity>
                    <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", "01")}}</cbc:CountrySubentityCode>
                @endif
                <cac:AddressLine>
                    <cbc:Line>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->address)}}</cbc:Line>
                </cac:AddressLine>
                <cac:Country>
                    <cbc:IdentificationCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->country->code)}}</cbc:IdentificationCode>
                    <cbc:Name languageID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->language->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->country->name)}}</cbc:Name>
                </cac:Country>
            </cac:RegistrationAddress>
            <cac:TaxScheme>
                <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->tax->code)}}</cbc:ID>
                <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->tax->name)}}</cbc:Name>
            </cac:TaxScheme>
        </cac:PartyTaxScheme>
        <cac:PartyLegalEntity>
            <cbc:RegistrationName>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->name)}}</cbc:RegistrationName>
            @if(in_array($typeDocument->id, ['15', '26', '19', '24', '16']))
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="31">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:CompanyID>
            @else
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:CompanyID>
            @endif
            <cac:CorporateRegistrationScheme>
                @if(isset($supplier) || ($typeDocument->id == '11' || $typeDocument->id == '13'))
                    <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $resolution->prefix)}}</cbc:ID>
                @endif
                <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->merchant_registration)}}</cbc:Name>
            </cac:CorporateRegistrationScheme>
        </cac:PartyLegalEntity>
        <cac:Contact>
            @if($user->company->identification_number != "222222222222")
                <cbc:Telephone>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->phone)}}</cbc:Telephone>
                <cbc:ElectronicMail>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->email)}}</cbc:ElectronicMail>
            @endif
        </cac:Contact>
    </cac:Party>
</cac:{{$node}}>
