import json, sys, os
sys.stdout.reconfigure(encoding='utf-8', errors='replace')

AR_DISTRICT = {
    'Damascus':           'دمشق',
    'Jebel Saman':        'جبل سمعان',
    'Al Bab':             'الباب',
    'Afrin':              'عفرين',
    "A'zaz":              'أعزاز',
    'Menbij':             'منبج',
    'Ain Al Arab':        'عين العرب',
    'As-Safira':          'السفيرة',
    'Jarablus':           'جرابلس',
    'Rural Damascus':     'ريف دمشق',
    'Duma':               'دوما',
    'Al Qutayfah':        'القطيفة',
    'At Tall':            'التل',
    'Yabroud':            'يبرود',
    'An Nabk':            'النبك',
    'Az-Zabdani':         'الزبداني',
    'Qatana':             'قطنا',
    'Darayya':            'داريا',
    'Homs':               'حمص',
    'Al-Qusayr':          'القصير',
    'Tall Kalakh':        'تلكلخ',
    'Ar-Rastan':          'الرستن',
    'Tadmor':             'تدمر',
    'Al Makhrim':         'المخرم',
    'Hama':               'حماة',
    'As-Suqaylabiyah':    'السقيلبية',
    'As-Salamiyeh':       'السلمية',
    'Masyaf':             'مصياف',
    'Muhradah':           'محردة',
    'Lattakia':           'اللاذقية',
    'Jablah':             'جبلة',
    'Al-Haffa':           'الحفة',
    'Al-Qardaha':         'القرداحة',
    'Idleb':              'إدلب',
    "Al Ma'ra":           'معرة النعمان',
    'Harim':              'حارم',
    'Jisr-Ash-Shugur':    'جسر الشغور',
    'Ariha':              'أريحا',
    'Al-Hasakeh':         'الحسكة',
    'Quamishli':          'القامشلي',
    'Al-Malikeyyeh':      'المالكية',
    'Ras Al Ain':         'رأس العين',
    'Deir-ez-Zor':        'دير الزور',
    'Abu Kamal':          'أبو كمال',
    'Al Mayadin':         'الميادين',
    'Tartous':            'طرطوس',
    'Banyas':             'بانياس',
    'Safita':             'صافيتا',
    'Dreikish':           'دريكيش',
    'Sheikh Badr':        'شيخ بدر',
    'Ar-Raqqa':           'الرقة',
    'Tell Abiad':         'تل أبيض',
    'Ath-Thawrah':        'الثورة',
    "Dar'a":              'درعا',
    'As-Sanamayn':        'الصنمين',
    "Izra'":              'إزرع',
    'As-Sweida':          'السويداء',
    'Salkhad':            'صلخد',
    'Shahba':             'شهبا',
    'Quneitra':           'القنيطرة',
    'Al Fiq':             'الفيق',
}

PARENT = {
    'Damascus':           'دمشق',
    'Jebel Saman':        'حلب',
    'Al Bab':             'حلب',
    'Afrin':              'حلب',
    "A'zaz":              'حلب',
    'Menbij':             'حلب',
    'Ain Al Arab':        'حلب',
    'As-Safira':          'حلب',
    'Jarablus':           'حلب',
    'Rural Damascus':     'ريف دمشق',
    'Duma':               'ريف دمشق',
    'Al Qutayfah':        'ريف دمشق',
    'At Tall':            'ريف دمشق',
    'Yabroud':            'ريف دمشق',
    'An Nabk':            'ريف دمشق',
    'Az-Zabdani':         'ريف دمشق',
    'Qatana':             'ريف دمشق',
    'Darayya':            'ريف دمشق',
    'Homs':               'حمص',
    'Al-Qusayr':          'حمص',
    'Tall Kalakh':        'حمص',
    'Ar-Rastan':          'حمص',
    'Tadmor':             'حمص',
    'Al Makhrim':         'حمص',
    'Hama':               'حماة',
    'As-Suqaylabiyah':    'حماة',
    'As-Salamiyeh':       'حماة',
    'Masyaf':             'حماة',
    'Muhradah':           'حماة',
    'Lattakia':           'اللاذقية',
    'Jablah':             'اللاذقية',
    'Al-Haffa':           'اللاذقية',
    'Al-Qardaha':         'اللاذقية',
    'Idleb':              'إدلب',
    "Al Ma'ra":           'إدلب',
    'Harim':              'إدلب',
    'Jisr-Ash-Shugur':    'إدلب',
    'Ariha':              'إدلب',
    'Al-Hasakeh':         'الحسكة',
    'Quamishli':          'الحسكة',
    'Al-Malikeyyeh':      'الحسكة',
    'Ras Al Ain':         'الحسكة',
    'Deir-ez-Zor':        'دير الزور',
    'Abu Kamal':          'دير الزور',
    'Al Mayadin':         'دير الزور',
    'Tartous':            'طرطوس',
    'Banyas':             'طرطوس',
    'Safita':             'طرطوس',
    'Dreikish':           'طرطوس',
    'Sheikh Badr':        'طرطوس',
    'Ar-Raqqa':           'الرقة',
    'Tell Abiad':         'الرقة',
    'Ath-Thawrah':        'الرقة',
    "Dar'a":              'درعا',
    'As-Sanamayn':        'درعا',
    "Izra'":              'درعا',
    'As-Sweida':          'السويداء',
    'Salkhad':            'السويداء',
    'Shahba':             'السويداء',
    'Quneitra':           'القنيطرة',
    'Al Fiq':             'القنيطرة',
}

CENTERS_GOV = {
    'دمشق':      [33.510, 36.291],
    'حلب':       [36.202, 37.161],
    'ريف دمشق': [33.580, 36.700],
    'حمص':       [34.730, 36.710],
    'حماة':      [35.132, 36.757],
    'اللاذقية':  [35.532, 35.791],
    'إدلب':      [35.930, 36.630],
    'الحسكة':    [36.512, 40.752],
    'دير الزور': [35.336, 40.141],
    'طرطوس':     [34.893, 35.887],
    'الرقة':     [35.953, 39.006],
    'درعا':      [32.625, 36.103],
    'السويداء':  [32.708, 36.566],
    'القنيطرة':  [33.125, 35.825],
}

AR1 = {
    'Damascus': 'دمشق', 'Aleppo': 'حلب', 'Rural Damascus': 'ريف دمشق',
    'Homs': 'حمص', 'Hama': 'حماة', 'Lattakia': 'اللاذقية', 'Idleb': 'إدلب',
    'Al-Hasakeh': 'الحسكة', 'Deir-ez-Zor': 'دير الزور', 'Tartous': 'طرطوس',
    'Ar-Raqqa': 'الرقة', "Dar'a": 'درعا', 'As-Sweida': 'السويداء', 'Quneitra': 'القنيطرة',
}

base = r'C:\Users\LENOVO\Desktop\back_authority\authority2\api2\front\assets\js\pages'

with open(base + r'\syria_adm1_simplified.geojson', encoding='utf-8') as f:
    adm1 = json.load(f)
with open(base + r'\syria_adm2_simplified.geojson', encoding='utf-8') as f:
    adm2 = json.load(f)

adm1_features = []
for ft in adm1['features']:
    en = ft['properties']['shapeName']
    ar = AR1.get(en, en)
    adm1_features.append({
        'type': 'Feature',
        'properties': {'name': ar, 'name_en': en, 'iso': ft['properties']['shapeISO']},
        'geometry': ft['geometry']
    })

adm2_features = []
for ft in adm2['features']:
    en = ft['properties']['shapeName']
    ar = AR_DISTRICT.get(en, en)
    gov = PARENT.get(en, '')
    adm2_features.append({
        'type': 'Feature',
        'properties': {'name': ar, 'name_en': en, 'governorate': gov},
        'geometry': ft['geometry']
    })

geojson1 = json.dumps({'type': 'FeatureCollection', 'features': adm1_features}, ensure_ascii=False, separators=(',', ':'))
geojson2 = json.dumps({'type': 'FeatureCollection', 'features': adm2_features}, ensure_ascii=False, separators=(',', ':'))
gov_list  = json.dumps([{'value': ar, 'label': ar} for ar in CENTERS_GOV], ensure_ascii=False, separators=(',', ':'))
centers   = json.dumps(CENTERS_GOV, ensure_ascii=False, separators=(',', ':'))

out = base + r'\syria-governorates.js'
with open(out, 'w', encoding='utf-8') as f:
    f.write('window.SYRIA_GOVERNORATES_GEOJSON = ' + geojson1 + ';\n\n')
    f.write('window.SYRIA_DISTRICTS_GEOJSON = ' + geojson2 + ';\n\n')
    f.write('window.SYRIA_GOVERNORATES_LIST = ' + gov_list + ';\n\n')
    f.write('window.SYRIA_GOVERNORATE_CENTERS = ' + centers + ';\n')

sz = round(os.path.getsize(out) / 1024)
print(f'OK  {sz} KB')
print(f'ADM1: {len(adm1_features)} محافظات')
print(f'ADM2: {len(adm2_features)} منطقة')
for g in adm2_features:
    p = g['properties']
    print(f"  {p['governorate']} <- {p['name']}")
