<?php

return [
    'required' => 'حقل :attribute مطلوب',
    'string' => 'حقل :attribute يجب أن يكون نصاً',
    'max' => [
        'string' => 'حقل :attribute يجب أن لا يتجاوز :max حرف',
        'file' => 'حقل :attribute يجب أن لا يتجاوز :max كيلوبايت',
        'array' => 'حقل :attribute يجب أن لا يتجاوز :max عنصر',
    ],
    'numeric' => 'حقل :attribute يجب أن يكون رقماً',
    'min' => [
        'numeric' => 'حقل :attribute يجب أن يكون على الأقل :min',
        'string' => 'حقل :attribute يجب أن يكون على الأقل :min حرف',
        'array' => 'حقل :attribute يجب أن يكون على الأقل :min عنصر',
    ],
    'exists' => 'القيمة المحددة في :attribute غير موجودة',
    'image' => 'حقل :attribute يجب أن يكون صورة',
    'mimes' => 'حقل :attribute يجب أن يكون ملف من نوع: :values',
    'array' => 'حقل :attribute يجب أن يكون مصفوفة',
    'regex' => 'صيغة حقل :attribute غير صحيحة',
    'product_id' => [
        'required' => 'الرجاء اختيار منتج.',
        'exists' => 'المنتج المحدد غير موجود.',
    ],
    'quantity' => [
        'required' => 'الرجاء تحديد الكمية.',
        'integer' => 'يجب أن تكون الكمية رقماً صحيحاً.',
        'min' => 'يجب أن تكون الكمية 1 على الأقل.',
    ],
]; 