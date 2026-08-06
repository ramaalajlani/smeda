import zipfile, re, json, sys

path = r'C:\Users\LENOVO\Downloads\التصنيف الوطني للأنشطة الاقتصادية الصادر بقرار رئيس مجلس الوزراء رقم 38 تاريخ 4 أيلول 2024 (3).xlsx'

with zipfile.ZipFile(path) as z:
    with z.open('xl/sharedStrings.xml') as f:
        ss = f.read().decode('utf-8')
    with z.open('xl/worksheets/sheet1.xml') as f:
        sheet = f.read().decode('utf-8')

strings = re.findall(r'<t[^>]*>(.*?)</t>', ss, re.DOTALL)

# اطبع كل الخلايا في الصفوف 4-30
print("=== Rows 4-30 ===")
for cell_m in re.finditer(r'<c r="([A-Z]+)(\d+)"([^>]*)>(.*?)</c>', sheet, re.DOTALL):
    col, row_num, attrs, inner = cell_m.group(1), int(cell_m.group(2)), cell_m.group(3), cell_m.group(4)
    if row_num < 4 or row_num > 30: continue
    v_m = re.search(r'<v>(.*?)</v>', inner)
    if not v_m: continue
    val = v_m.group(1)
    if 't="s"' in attrs:
        val = strings[int(val)]
    else:
        try: val = float(val)
        except: pass
    print(f"  {col}{row_num}: {repr(str(val)[:70])}")
