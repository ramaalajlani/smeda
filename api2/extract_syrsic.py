"""
SyrSIC extractor - corrected column mapping:
  A = seq no
  B = section label text  (e.g. 'الباب ( الف )')
  C = division code       (1 digit,  e.g. 1.0)
  D = group code          (2 digits, e.g. 11.0)
  E = branch code         (3 digits, e.g. 111.0)
  F = class code          (4 digits, e.g. 1110.0)
  G = activity code       (5 digits, e.g. 11101.0)
  H = name / description  (ALL levels)
  I = notes
"""
import json, os, sys, re
import openpyxl

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

XLSX = r'C:\Users\LENOVO\Downloads\التصنيف الوطني للأنشطة الاقتصادية الصادر بقرار رئيس مجلس الوزراء رقم 38 تاريخ 4 أيلول 2024 (3).xlsx'
OUT  = r'C:\Users\LENOVO\Desktop\back_authority\authority2\api2\front\assets\js\pages\syrsic_full.json'
PROG = r'C:\Users\LENOVO\Desktop\back_authority\authority2\api2\syrsic_progress.json'

# skip if already done
if os.path.exists(OUT):
    with open(OUT, encoding='utf-8') as f:
        ex = json.load(f)
    if ex and ex[0].get('name','') and not ex[0]['name'].replace('.','').isdigit():
        ta = sum(len(c['activities']) for s in ex for d in s['divisions']
                 for g in d['groups'] for b in g['branches'] for c in b['classes'])
        print(f"[OK] exists: {len(ex)} sections, {ta} activities")
        sys.exit(0)

SECTION_AR = {
    'الف':'A','باء':'B','جيم':'C','دال':'D','هاء':'E','واو':'F',
    'زاي':'G','حاء':'H','طاء':'I','ياء':'J','كاف':'K','لام':'L',
    'ميم':'M','نون':'N','سين':'O','عين':'P','فاء':'Q','صاد':'R','قاف':'S'
}

def clean(v):
    return str(v).strip() if v is not None else ''

def to_code(v):
    try: return str(int(float(str(v))))
    except: return None

print("[1/3] opening workbook...")
wb = openpyxl.load_workbook(XLSX, read_only=True, data_only=True)
ws = wb.active

db  = []
cur = {'section': None, 'division': None, 'group': None, 'branch': None, 'cls': None}
done = 0
TOTAL = 5646

print("[2/3] reading rows...")
for row in ws.iter_rows(min_row=5, values_only=True):
    # A=0, B=1, C=2, D=3, E=4, F=5, G=6, H=7, I=8
    B = row[1] if len(row) > 1 else None
    C = row[2] if len(row) > 2 else None
    D = row[3] if len(row) > 3 else None
    E = row[4] if len(row) > 4 else None
    F = row[5] if len(row) > 5 else None
    G = row[6] if len(row) > 6 else None
    H = clean(row[7]) if len(row) > 7 else ''

    b_str = clean(B)

    # Section: B has 'باب'
    if b_str and 'باب' in b_str:
        m = re.search(r'\(\s*(\S+)\s*\)', b_str)
        ar   = m.group(1) if m else '?'
        code = SECTION_AR.get(ar, ar)
        sec  = {'code': code, 'ar_code': ar, 'name': H, 'divisions': []}
        db.append(sec)
        cur  = {'section': sec, 'division': None, 'group': None, 'branch': None, 'cls': None}

    # Division: C has code (1 digit)
    elif C is not None:
        ci = to_code(C)
        if ci and cur['section'] is not None:
            obj = {'code': ci, 'name': H, 'groups': []}
            cur['section']['divisions'].append(obj)
            cur.update({'division': obj, 'group': None, 'branch': None, 'cls': None})

    # Group: D has code (2 digits)
    elif D is not None:
        ci = to_code(D)
        if ci and cur['division'] is not None:
            obj = {'code': ci, 'name': H, 'branches': []}
            cur['division']['groups'].append(obj)
            cur.update({'group': obj, 'branch': None, 'cls': None})

    # Branch: E has code (3 digits)
    elif E is not None:
        ci = to_code(E)
        if ci and cur['group'] is not None:
            obj = {'code': ci, 'name': H, 'classes': []}
            cur['group']['branches'].append(obj)
            cur.update({'branch': obj, 'cls': None})

    # Class: F has code (4 digits)
    elif F is not None:
        ci = to_code(F)
        if ci and cur['branch'] is not None:
            obj = {'code': ci, 'name': H, 'activities': []}
            cur['branch']['classes'].append(obj)
            cur['cls'] = obj

    # Activity: G has code (5 digits)
    elif G is not None:
        ci = to_code(G)
        if ci and cur['cls'] is not None:
            cur['cls']['activities'].append({'code': ci, 'name': H})

    done += 1
    if done % 1000 == 0:
        pct = round(done / TOTAL * 100)
        with open(PROG, 'w', encoding='utf-8') as pf:
            json.dump({'done': done, 'sections': len(db)}, pf)
        print(f"    {pct}% ({done}/{TOTAL})", flush=True)

wb.close()

ts = len(db)
td = sum(len(s['divisions']) for s in db)
tg = sum(len(d['groups']) for s in db for d in s['divisions'])
tb = sum(len(g['branches']) for s in db for d in s['divisions'] for g in d['groups'])
tc = sum(len(b['classes']) for s in db for d in s['divisions'] for g in d['groups'] for b in g['branches'])
ta = sum(len(c['activities']) for s in db for d in s['divisions'] for g in d['groups'] for b in g['branches'] for c in b['classes'])
print(f"\n=== sections={ts} divisions={td} groups={tg} branches={tb} classes={tc} activities={ta} ===")

# sample
for s in db:
    if s['code'] == 'C':
        print(f"\n[C] {s['name'][:60]}")
        for d in s['divisions'][:3]:
            print(f"  [{d['code']}] {d['name'][:55]}")
            for g in d['groups'][:1]:
                print(f"    [{g['code']}] {g['name'][:50]}")
                for b in g['branches'][:1]:
                    print(f"      [{b['code']}] {b['name'][:45]}")
                    for c in b['classes'][:1]:
                        print(f"        [{c['code']}] {c['name'][:40]}")
                        for a in c['activities'][:3]:
                            print(f"          [{a['code']}] {a['name'][:55]}")

print("\n[3/3] saving JSON...")
with open(OUT, 'w', encoding='utf-8') as f:
    json.dump(db, f, ensure_ascii=False, indent=2)
print(f"[DONE] {OUT}")
if os.path.exists(PROG):
    os.remove(PROG)
