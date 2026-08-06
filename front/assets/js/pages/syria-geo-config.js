/** @typedef {[number, number]} LatLngTuple */

window.SYRIA_GOVERNORATE_CENTERS = {
  'دمشق': [33.513, 36.291],
  'حلب': [36.202, 37.161],
  'ريف دمشق': [33.583, 36.450],
  'حمص': [34.732, 36.713],
  'حماة': [35.132, 36.757],
  'اللاذقية': [35.532, 35.791],
  'إدلب': [35.931, 36.634],
  'الحسكة': [36.512, 40.752],
  'دير الزور': [35.336, 40.141],
  'طرطوس': [34.893, 35.887],
  'الرقة': [35.953, 39.006],
  'درعا': [32.625, 36.103],
  'السويداء': [32.708, 36.566],
  'القنيطرة': [33.125, 35.825],
};

window.SYRIA_GOVERNORATES_LIST = Object.entries(window.SYRIA_GOVERNORATE_CENTERS).map(
  function ([label, value]) {
    return { value: label, label: label };
  }
);
