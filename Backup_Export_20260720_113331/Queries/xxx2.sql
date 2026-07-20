-- Query: xxx2
-- Type: 0

SELECT IIf(Nz([pci_subhead],"")="","(CSRF/ For Center)",[pci_subhead]) AS Subhead, [xxx2-3].pci_subtype, Count([xxx2-3].pcs_id) AS CountOfpcs_id, Sum([xxx2-3].price) AS SumOfprice
FROM [xxx2-3]
GROUP BY IIf(Nz([pci_subhead],"")="","(CSRF/ For Center)",[pci_subhead]), [xxx2-3].pci_subtype;

