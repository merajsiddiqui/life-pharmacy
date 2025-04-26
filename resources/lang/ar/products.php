<?php

return [
    'messages' => [
        'created' => 'تم إنشاء المنتج بنجاح',
        'creation_failed' => 'فشل في إنشاء المنتج',
        'retrieved' => 'تم استرجاع المنتج بنجاح',
        'list_retrieved' => 'تم استرجاع المنتجات بنجاح',
        'updated' => 'تم تحديث المنتج بنجاح',
        'update_failed' => 'فشل في تحديث المنتج',
        'deleted' => 'تم حذف المنتج بنجاح',
        'delete_failed' => 'فشل في حذف المنتج',
        'not_found' => 'المنتج غير موجود',
        'retrieval_failed' => 'فشل في استرجاع المنتج',
    ],
    'validation' => [
        'name_required' => 'اسم المنتج مطلوب',
        'name_string' => 'يجب أن يكون اسم المنتج نصاً',
        'name_max' => 'يجب ألا يتجاوز اسم المنتج :max حرفاً',
        'description_required' => 'وصف المنتج مطلوب',
        'description_string' => 'يجب أن يكون وصف المنتج نصاً',
        'price_required' => 'سعر المنتج مطلوب',
        'price_numeric' => 'يجب أن يكون سعر المنتج رقماً',
        'price_min' => 'يجب أن يكون سعر المنتج 0 على الأقل',
        'stock_required' => 'مخزون المنتج مطلوب',
        'stock_integer' => 'يجب أن يكون مخزون المنتج رقماً صحيحاً',
        'stock_min' => 'لا يمكن أن يكون مخزون المنتج سالباً',
        'category_id_required' => 'الفئة مطلوبة',
        'category_id_exists' => 'الفئة المحددة غير موجودة',
    ],
    'attributes' => [
        'name' => 'الاسم',
        'description' => 'الوصف',
        'price' => 'السعر',
        'stock' => 'المخزون',
        'category_id' => 'الفئة',
        'images' => 'الصور',
    ],
];
