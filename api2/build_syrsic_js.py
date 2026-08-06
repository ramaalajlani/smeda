"""
يحوّل syrsic_full.json إلى ملف JavaScript جاهز للمتصفح
مع فهرس بحث مسطّح وكامل لجميع المستويات
"""
import json, os, sys

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

IN  = r'C:\Users\LENOVO\Desktop\back_authority\authority2\api2\front\assets\js\pages\syrsic_full.json'
OUT = r'C:\Users\LENOVO\Desktop\back_authority\authority2\api2\front\assets\js\pages\syrsic-data.js'

with open(IN, encoding='utf-8') as f:
    db = json.load(f)

# بنِ فهرس مسطّح للبحث
index = []
for s in db:
    s_path = s['name']
    for d in s['divisions']:
        d_path = f"{s['name']} ← {d['name']}"
        for g in d['groups']:
            g_path = f"{d_path} ← {g['name']}"
            for b in g['branches']:
                b_path = f"{g_path} ← {b['name']}"
                for c in b['classes']:
                    c_path = f"{b_path} ← {c['name']}"
                    for a in c['activities']:
                        index.append({
                            'code': a['code'],
                            'name': a['name'],
                            'path': c_path,
                            'level': 'activity',
                            'sectionCode': s['code'],
                            'sectionName': s['name'],
                            'divCode': d['code'],
                            'grpCode': g['code'],
                            'branchCode': b['code'],
                            'classCode': c['code'],
                        })
                    # الفئة نفسها كـ entry
                    index.append({
                        'code': c['code'],
                        'name': c['name'],
                        'path': b_path,
                        'level': 'class',
                        'sectionCode': s['code'],
                        'sectionName': s['name'],
                        'divCode': d['code'],
                        'grpCode': g['code'],
                        'branchCode': b['code'],
                        'classCode': c['code'],
                    })

print(f"index entries: {len(index)}")

js_db    = json.dumps(db,    ensure_ascii=False, separators=(',',':'))
js_index = json.dumps(index, ensure_ascii=False, separators=(',',':'))

js = f"""/* SyrSIC V0.1 — التصنيف الوطني للأنشطة الاقتصادية - قرار رئيس مجلس الوزراء رقم 38 / 2024
   أبواب={len(db)}, أنشطة={sum(len(c['activities']) for s in db for d in s['divisions'] for g in d['groups'] for b in g['branches'] for c in b['classes'])}
   تم الاستخراج التلقائي من ملف الإكسل الرسمي */
window.SYRSIC_DB = {js_db};

/* فهرس البحث المسطّح (الأنشطة + الفئات) - {len(index)} عنصر */
window.SYRSIC_INDEX = {js_index};

/**
 * بحث فوري - يقبل اسم النشاط أو بادئة كودية
 * @param {{string}} q   نص البحث
 * @param {{number}} max أقصى عدد نتائج
 */
window.SYRSIC_SEARCH = function(q, max) {{
  max = max || 15;
  q = (q || '').trim();
  if (!q || q.length < 2) return [];

  var isNum = /^\\d/.test(q);
  var qLow  = q.toLowerCase();
  var out   = [];
  var seen  = {{}};

  for (var i = 0; i < window.SYRSIC_INDEX.length; i++) {{
    var item = window.SYRSIC_INDEX[i];
    var match = false;

    if (isNum) {{
      match = item.code.indexOf(q) === 0;          // prefix match on code
    }} else {{
      match = item.name.indexOf(q) !== -1           // name contains
           || item.path.indexOf(q) !== -1;          // path contains
    }}

    if (match && !seen[item.code]) {{
      seen[item.code] = 1;
      out.push(item);
      if (out.length >= max) break;
    }}
  }}

  // الأنشطة التفصيلية أولاً
  out.sort(function(a,b) {{
    if (a.level === b.level) return 0;
    return a.level === 'activity' ? -1 : 1;
  }});

  return out.slice(0, max);
}};

/**
 * جلب تفاصيل نشاط بكوده
 */
window.SYRSIC_BY_CODE = function(code) {{
  for (var i = 0; i < window.SYRSIC_INDEX.length; i++) {{
    if (window.SYRSIC_INDEX[i].code === code) return window.SYRSIC_INDEX[i];
  }}
  return null;
}};
"""

with open(OUT, 'w', encoding='utf-8') as f:
    f.write(js)

size_kb = round(os.path.getsize(OUT) / 1024)
print(f"[DONE] {OUT}  ({size_kb} KB)")
