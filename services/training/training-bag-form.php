<?php
$basePath = '../../';
$pageTitle = 'إضافة / تعديل حقيبة تدريبية';
$activePage = 'training-bag-form';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>
  <style>
    :root{--c:#1a6b5a;--cb:rgba(26,107,90,.12);}
    .bag-form-card{background:#fff;border:1px solid var(--cb);border-radius:18px;padding:20px;margin-bottom:16px;}
    .bag-tabs{display:flex;gap:0;border-bottom:2px solid var(--cb);margin-bottom:16px;flex-wrap:wrap;}
    .bag-tab{padding:10px 16px;font-weight:700;color:#6b7280;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;}
    .bag-tab.active{color:var(--c);border-bottom-color:var(--c);}
    .bag-panel{display:none;}.bag-panel.active{display:block;}
    .file-hint{font-size:.8rem;color:#6b7280;margin-top:4px;}
    .file-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:8px;background:#e8f8f3;color:#1a6b5a;font-size:.85rem;}
  </style>
</head>
<body>
<?php include '../../includes/layout/app-shell-open.php'; ?>

<section class="section pt-3 pb-4">
  <div class="container" style="max-width:960px">
    <div class="breadcrumb-soft mb-3">
      <a href="training-bags-hub.php">الحقائب التدريبية</a>
      <span>/</span>
      <span id="pageCrumb">إضافة حقيبة</span>
    </div>
    <h1 class="fw-bold mb-3" id="pageTitle">إضافة حقيبة تدريبية</h1>
    <div id="formMsg" class="alert d-none" role="alert"></div>

    <form id="bagForm" enctype="multipart/form-data">
      <div class="bag-form-card">
        <div class="bag-tabs" id="bagTabs">
          <div class="bag-tab active" data-tab="basic">1. الأساسيات</div>
          <div class="bag-tab" data-tab="category">2. التصنيف</div>
          <div class="bag-tab" data-tab="details">3. التفاصيل</div>
          <div class="bag-tab" data-tab="files">4. الملفات</div>
          <div class="bag-tab" data-tab="publish">5. النشر</div>
        </div>

        <div class="bag-panel active" data-panel="basic">
          <div class="row g-3">
            <div class="col-md-8"><label class="form-label fw-bold">اسم الحقيبة (عربي) *</label><input class="form-control" id="name" required></div>
            <div class="col-md-4"><label class="form-label fw-bold">الرمز</label><input class="form-control" id="code" placeholder="KIT-0001"></div>
            <div class="col-md-6"><label class="form-label fw-bold">الاسم (إنجليزي)</label><input class="form-control" id="name_en"></div>
            <div class="col-md-6"><label class="form-label fw-bold">المستوى</label>
              <select class="form-select" id="level">
                <option value="">—</option>
                <option value="beginner">مبتدئ</option>
                <option value="intermediate">متوسط</option>
                <option value="advanced">متقدم</option>
              </select>
            </div>
            <div class="col-12"><label class="form-label fw-bold">وصف مختصر</label><textarea class="form-control" id="short_description" rows="2"></textarea></div>
            <div class="col-12"><label class="form-label fw-bold">وصف تفصيلي</label><textarea class="form-control" id="description" rows="3"></textarea></div>
          </div>
        </div>

        <div class="bag-panel" data-panel="category">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">التصنيف الرئيسي</label><select class="form-select" id="category_id"><option value="">—</option></select></div>
            <div class="col-md-6"><label class="form-label fw-bold">التخصص / التصنيف الفرعي</label><select class="form-select" id="subcategory_id"><option value="">—</option></select></div>
            <div class="col-md-6"><label class="form-label fw-bold">القطاع (legacy)</label><input class="form-control" id="sector"></div>
          </div>
        </div>

        <div class="bag-panel" data-panel="details">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-bold">عدد الساعات</label><input type="number" min="0" class="form-control" id="hours" value="0"></div>
            <div class="col-md-4"><label class="form-label fw-bold">عدد الأيام المقترحة</label><input type="number" min="0" class="form-control" id="suggested_days"></div>
            <div class="col-12"><label class="form-label fw-bold">الفئة المستهدفة</label><textarea class="form-control" id="target_audience" rows="2"></textarea></div>
            <div class="col-12"><label class="form-label fw-bold">المتطلبات السابقة</label><textarea class="form-control" id="prerequisites" rows="2"></textarea></div>
            <div class="col-12"><label class="form-label fw-bold">أهداف الحقيبة</label><textarea class="form-control" id="objective" rows="2"></textarea></div>
            <div class="col-12"><label class="form-label fw-bold">المخرجات / المهارات المتوقعة</label><textarea class="form-control" id="expected_outcomes" rows="2"></textarea></div>
          </div>
        </div>

        <div class="bag-panel" data-panel="files">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label fw-bold">الملف الترويجي</label>
              <input type="file" class="form-control" id="promotional_file" accept=".pdf,.doc,.docx,.ppt,.pptx,application/pdf">
              <div class="file-hint">PDF أو Word أو PowerPoint — للعرض التعريفي.</div>
              <div id="promoStatus" class="mt-2"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">ملف الحقيبة التدريبية (PDF) *</label>
              <input type="file" class="form-control" id="training_bag_file" accept=".pdf,application/pdf">
              <div class="file-hint">PDF فقط — محمي ولا يُعرض إلا للمخولين.</div>
              <div id="bagFileStatus" class="mt-2"></div>
            </div>
          </div>
        </div>

        <div class="bag-panel" data-panel="publish">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">حالة سير العمل</label>
              <select class="form-select" id="workflow_status">
                <option value="draft">مسودة</option>
                <option value="under_review">قيد المراجعة</option>
                <option value="approved">معتمدة</option>
                <option value="published">منشورة</option>
                <option value="inactive">غير نشطة</option>
                <option value="archived">مؤرشفة</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 justify-content-end flex-wrap">
        <a href="training-kits-list.php" class="btn btn-outline-secondary">إلغاء</a>
        <button type="submit" class="btn btn-primary" id="saveBtn"><i class="bi bi-save"></i> حفظ الحقيبة</button>
      </div>
    </form>
  </div>
</section>

<?php include '../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/training-bag-form.js?v=1.0"></script>
</body>
</html>
