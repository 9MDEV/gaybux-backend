<?php

return [

    /*
    |---------------------------------------------------------------------------
    | Validation Language Lines
    |---------------------------------------------------------------------------
    |
    | ข้อความที่ตามมาคือข้อความผิดพลาดที่ใช้ในคลาส Validator
    | บางกฎมีหลายเวอร์ชันเช่นกฎขนาด ข้อความเหล่านี้สามารถปรับแต่งได้ตามต้องการ
    |
    */

    'accepted' => 'ช่อง :attribute ต้องได้รับการยอมรับ.',
    'accepted_if' => 'ช่อง :attribute ต้องได้รับการยอมรับเมื่อ :other มีค่าเป็น :value.',
    'active_url' => 'ช่อง :attribute ต้องเป็น URL ที่ถูกต้อง.',
    'after' => 'ช่อง :attribute ต้องเป็นวันที่หลังจาก :date.',
    'after_or_equal' => 'ช่อง :attribute ต้องเป็นวันที่หลังหรือเท่ากับ :date.',
    'alpha' => 'ช่อง :attribute ต้องประกอบด้วยตัวอักษรเท่านั้น.',
    'alpha_dash' => 'ช่อง :attribute ต้องประกอบด้วยตัวอักษร, ตัวเลข, ขีดกลาง และขีดล่างเท่านั้น.',
    'alpha_num' => 'ช่อง :attribute ต้องประกอบด้วยตัวอักษรและตัวเลขเท่านั้น.',
    'array' => 'ช่อง :attribute ต้องเป็นอาเรย์.',
    'ascii' => 'ช่อง :attribute ต้องประกอบด้วยตัวอักษรและสัญลักษณ์ที่เป็น ASCII เท่านั้น.',
    'before' => 'ช่อง :attribute ต้องเป็นวันที่ก่อน :date.',
    'before_or_equal' => 'ช่อง :attribute ต้องเป็นวันที่ก่อนหรือเท่ากับ :date.',
    'between' => [
        'array' => 'ช่อง :attribute ต้องมีระหว่าง :min และ :max รายการ.',
        'file' => 'ช่อง :attribute ต้องมีขนาดระหว่าง :min และ :max กิโลไบต์.',
        'numeric' => 'ช่อง :attribute ต้องมีค่าระหว่าง :min และ :max.',
        'string' => 'ช่อง :attribute ต้องมีความยาวระหว่าง :min และ :max ตัวอักษร.',
    ],
    'boolean' => 'ช่อง :attribute ต้องเป็นค่า true หรือ false.',
    'can' => 'ช่อง :attribute มีค่าที่ไม่ได้รับอนุญาต.',
    'confirmed' => 'ช่อง :attribute ยืนยันไม่ตรงกัน.',
    'contains' => 'ช่อง :attribute ขาดค่าที่จำเป็น.',
    'current_password' => 'รหัสผ่านไม่ถูกต้อง.',
    'date' => 'ช่อง :attribute ต้องเป็นวันที่ที่ถูกต้อง.',
    'date_equals' => 'ช่อง :attribute ต้องเป็นวันที่เดียวกับ :date.',
    'date_format' => 'ช่อง :attribute ต้องตรงกับรูปแบบ :format.',
    'decimal' => 'ช่อง :attribute ต้องมีทศนิยม :decimal ตำแหน่ง.',
    'declined' => 'ช่อง :attribute ต้องถูกปฏิเสธ.',
    'declined_if' => 'ช่อง :attribute ต้องถูกปฏิเสธเมื่อ :other มีค่าเป็น :value.',
    'different' => 'ช่อง :attribute และ :other ต้องแตกต่างกัน.',
    'digits' => 'ช่อง :attribute ต้องมี :digits หลัก.',
    'digits_between' => 'ช่อง :attribute ต้องมีระหว่าง :min และ :max หลัก.',
    'dimensions' => 'ช่อง :attribute มีขนาดภาพที่ไม่ถูกต้อง.',
    'distinct' => 'ช่อง :attribute มีค่าซ้ำ.',
    'doesnt_end_with' => 'ช่อง :attribute ต้องไม่ลงท้ายด้วยหนึ่งในค่าต่อไปนี้: :values.',
    'doesnt_start_with' => 'ช่อง :attribute ต้องไม่เริ่มต้นด้วยหนึ่งในค่าต่อไปนี้: :values.',
    'email' => 'ช่อง :attribute ต้องเป็นอีเมลที่ถูกต้อง.',
    'ends_with' => 'ช่อง :attribute ต้องลงท้ายด้วยหนึ่งในค่าต่อไปนี้: :values.',
    'enum' => 'ช่อง :attribute ที่เลือกไม่ถูกต้อง.',
    'exists' => 'ช่อง :attribute ที่เลือกไม่ถูกต้อง.',
    'extensions' => 'ช่อง :attribute ต้องมีนามสกุลไฟล์ต่อไปนี้: :values.',
    'file' => 'ช่อง :attribute ต้องเป็นไฟล์.',
    'filled' => 'ช่อง :attribute ต้องมีค่า.',
    'gt' => [
        'array' => 'ช่อง :attribute ต้องมีมากกว่า :value รายการ.',
        'file' => 'ช่อง :attribute ต้องมีขนาดมากกว่า :value กิโลไบต์.',
        'numeric' => 'ช่อง :attribute ต้องมากกว่า :value.',
        'string' => 'ช่อง :attribute ต้องมีมากกว่า :value ตัวอักษร.',
    ],
    'gte' => [
        'array' => 'ช่อง :attribute ต้องมี :value รายการหรือมากกว่า.',
        'file' => 'ช่อง :attribute ต้องมีขนาดมากกว่าหรือเท่ากับ :value กิโลไบต์.',
        'numeric' => 'ช่อง :attribute ต้องมากกว่าหรือเท่ากับ :value.',
        'string' => 'ช่อง :attribute ต้องมีความยาวมากกว่าหรือเท่ากับ :value ตัวอักษร.',
    ],
    'hex_color' => 'ช่อง :attribute ต้องเป็นสี Hexadecimal ที่ถูกต้อง.',
    'image' => 'ช่อง :attribute ต้องเป็นไฟล์ภาพ.',
    'in' => 'ช่อง :attribute ที่เลือกไม่ถูกต้อง.',
    'in_array' => 'ช่อง :attribute ต้องมีอยู่ใน :other.',
    'integer' => 'ช่อง :attribute ต้องเป็นจำนวนเต็ม.',
    'ip' => 'ช่อง :attribute ต้องเป็นที่อยู่ IP ที่ถูกต้อง.',
    'ipv4' => 'ช่อง :attribute ต้องเป็นที่อยู่ IPv4 ที่ถูกต้อง.',
    'ipv6' => 'ช่อง :attribute ต้องเป็นที่อยู่ IPv6 ที่ถูกต้อง.',
    'json' => 'ช่อง :attribute ต้องเป็นสตริง JSON ที่ถูกต้อง.',
    'list' => 'ช่อง :attribute ต้องเป็นรายการ.',
    'lowercase' => 'ช่อง :attribute ต้องเป็นตัวพิมพ์เล็ก.',
    'lt' => [
        'array' => 'ช่อง :attribute ต้องมีน้อยกว่า :value รายการ.',
        'file' => 'ช่อง :attribute ต้องมีขนาดน้อยกว่า :value กิโลไบต์.',
        'numeric' => 'ช่อง :attribute ต้องน้อยกว่า :value.',
        'string' => 'ช่อง :attribute ต้องมีน้อยกว่า :value ตัวอักษร.',
    ],
    'lte' => [
        'array' => 'ช่อง :attribute ต้องมีไม่เกิน :value รายการ.',
        'file' => 'ช่อง :attribute ต้องมีขนาดน้อยกว่าหรือเท่ากับ :value กิโลไบต์.',
        'numeric' => 'ช่อง :attribute ต้องน้อยกว่าหรือเท่ากับ :value.',
        'string' => 'ช่อง :attribute ต้องมีความยาวน้อยกว่าหรือเท่ากับ :value ตัวอักษร.',
    ],
    'mac_address' => 'ช่อง :attribute ต้องเป็นที่อยู่ MAC ที่ถูกต้อง.',
    'max' => [
        'array' => 'ช่อง :attribute ต้องมีไม่เกิน :max รายการ.',
        'file' => 'ช่อง :attribute ต้องมีขนาดไม่เกิน :max กิโลไบต์.',
        'numeric' => 'ช่อง :attribute ต้องไม่เกิน :max.',
        'string' => 'ช่อง :attribute ต้องไม่เกิน :max ตัวอักษร.',
    ],
    'max_digits' => 'ช่อง :attribute ต้องมีไม่เกิน :max หลัก.',
    'mimes' => 'ช่อง :attribute ต้องเป็นไฟล์ประเภท: :values.',
    'mimetypes' => 'ช่อง :attribute ต้องเป็นไฟล์ประเภท: :values.',
    'min' => [
        'array' => 'ช่อง :attribute ต้องมีอย่างน้อย :min รายการ.',
        'file' => 'ช่อง :attribute ต้องมีขนาดอย่างน้อย :min กิโลไบต์.',
        'numeric' => 'ช่อง :attribute ต้องมีค่าตั้งแต่ :min.',
        'string' => 'ช่อง :attribute ต้องมีความยาวอย่างน้อย :min ตัวอักษร.',
    ],
    'min_digits' => 'ช่อง :attribute ต้องมีอย่างน้อย :min หลัก.',
    'missing' => 'ช่อง :attribute ต้องหายไป.',
    'missing_if' => 'ช่อง :attribute ต้องหายไปเมื่อ :other มีค่าเป็น :value.',
    'missing_unless' => 'ช่อง :attribute ต้องหายไปเว้นแต่ :other จะมีค่าเป็น :value.',
    'missing_with' => 'ช่อง :attribute ต้องหายไปเมื่อ :values มีค่า.',
    'missing_with_all' => 'ช่อง :attribute ต้องหายไปเมื่อ :values ทั้งหมดมีค่า.',
    'multiple_of' => 'ช่อง :attribute ต้องเป็นจำนวนที่เป็นผลคูณของ :value.',
    'not_in' => 'ช่อง :attribute ที่เลือกไม่ถูกต้อง.',
    'not_regex' => 'ช่อง :attribute มีรูปแบบที่ไม่ถูกต้อง.',
    'numeric' => 'ช่อง :attribute ต้องเป็นตัวเลข.',
    'password' => [
        'letters' => 'ช่อง :attribute ต้องประกอบด้วยตัวอักษร.',
        'mixed' => 'ช่อง :attribute ต้องประกอบด้วยตัวอักษรพิมพ์ใหญ่และพิมพ์เล็ก.',
        'numbers' => 'ช่อง :attribute ต้องประกอบด้วยตัวเลข.',
        'symbols' => 'ช่อง :attribute ต้องประกอบด้วยสัญลักษณ์.',
        'uncompromised' => 'ช่อง :attribute ได้รั่วไหลในข้อมูลที่ถูกละเมิด.',
    ],
    'present' => 'ช่อง :attribute ต้องปรากฏอยู่.',
    'prohibited' => 'ช่อง :attribute ต้องห้าม.',
    'prohibited_if' => 'ช่อง :attribute ต้องห้ามเมื่อ :other มีค่าเป็น :value.',
    'prohibited_unless' => 'ช่อง :attribute ต้องห้ามเว้นแต่ :other จะมีค่าเป็น :value.',
    'prohibits' => 'ช่อง :attribute ห้าม :other.',
    'regex' => 'ช่อง :attribute มีรูปแบบที่ไม่ถูกต้อง.',
    'required' => 'ช่อง :attribute เป็นข้อมูลที่จำเป็น.',
    'required_array_keys' => 'ช่อง :attribute ต้องประกอบด้วยคีย์ของรายการ: :values.',
    'required_if' => 'ช่อง :attribute จำเป็นต้องมีเมื่อ :other เป็น :value.',
    'required_if_accepted' => 'ช่อง :attribute จำเป็นต้องมีเมื่อ :other ได้รับการยอมรับ.',
    'required_unless' => 'ช่อง :attribute จำเป็นต้องมีเว้นแต่ :other จะเป็น :value.',
    'required_with' => 'ช่อง :attribute จำเป็นต้องมีเมื่อ :values มีค่า.',
    'required_with_all' => 'ช่อง :attribute จำเป็นต้องมีเมื่อ :values ทั้งหมดมีค่า.',
    'required_without' => 'ช่อง :attribute จำเป็นต้องมีเมื่อ :values ไม่มีค่า.',
    'required_without_all' => 'ช่อง :attribute จำเป็นต้องมีเมื่อ :values ทั้งหมดไม่มีค่า.',
    'same' => 'ช่อง :attribute และ :other ต้องตรงกัน.',
    'size' => [
        'array' => 'ช่อง :attribute ต้องมี :size รายการ.',
        'file' => 'ช่อง :attribute ต้องมีขนาด :size กิโลไบต์.',
        'numeric' => 'ช่อง :attribute ต้องมีค่า :size.',
        'string' => 'ช่อง :attribute ต้องมีความยาว :size ตัวอักษร.',
    ],
    'starts_with' => 'ช่อง :attribute ต้องเริ่มต้นด้วยค่าต่อไปนี้: :values.',
    'string' => 'ช่อง :attribute ต้องเป็นสตริง.',
    'timezone' => 'ช่อง :attribute ต้องเป็นเขตเวลาที่ถูกต้อง.',
    'unique' => 'ช่อง :attribute ได้ถูกใช้ไปแล้ว.',
    'uploaded' => 'ช่อง :attribute อัพโหลดไม่สำเร็จ.',
    'url' => 'ช่อง :attribute ต้องเป็น URL ที่ถูกต้อง.',
    'uuid' => 'ช่อง :attribute ต้องเป็น UUID ที่ถูกต้อง.',
];
