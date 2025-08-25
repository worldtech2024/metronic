<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerExport implements FromArray, WithStyles, WithTitle
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function array(): array
    {
        $rows = [];

        // الصف الأول: required/optional
        $rows[] = [
            'required',
            'required',
            'required',
            'optional',
            'optional',
            'optional',
            'optional',
            'VAT Number',
            'commercial register'
        ];

        // الصف الثاني: رؤوس الأعمدة
        $rows[] = [
            'customer_name',
            'email',
            'phone',
            'address_1',
            'Address_2',
            'city',
            'country',
            'vat_number',
            'cr_number',
        ];

        // البيانات الفعلية
        foreach ($this->users as $user) {
            $rows[] = [
                $user->name,
                $user->email,
                $user->phone,
                $user->address,
                $user->address2,
                $user->city,
                $user->country,
                $user->taxNum,
                $user->commercialRegister,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // توسيع الأعمدة
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }

        // تنسيق الصف الأول والثاني
        $sheet->getStyle('A1:I2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE699'], // لون أصفر خفيف
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);

        // تنسيق بقية الصفوف
        $sheet->getStyle('A3:I' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);
    }

    public function title(): string
    {
        return 'Customers Sheet';
    }
}