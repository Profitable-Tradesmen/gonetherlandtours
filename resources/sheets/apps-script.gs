/**
 * Receives enquiries from the website and adds each one as a row in this spreadsheet.
 * Paste this whole file into Extensions > Apps Script, put your secret key on the line below, then deploy it as a web app.
 */

// 1. Paste the secret key from the website (Admin > Google Sheets) between the quotes. Keep this script private.
var SECRET = 'PASTE-YOUR-SECRET-KEY-HERE';

// 2. The tab that receives the rows. It is created for you if it does not exist.
var SHEET_NAME = 'Enquiries';

function doPost(e) {
  var lock = LockService.getScriptLock();
  try {
    lock.waitLock(20000);

    var body = JSON.parse(e.postData.contents);
    if (SECRET === 'PASTE-YOUR-SECRET-KEY-HERE' || !body || body.secret !== SECRET) {
      return reply({ ok: false, error: 'The secret key does not match. Paste the same key into the script and into the website.' });
    }

    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName(SHEET_NAME) || ss.insertSheet(SHEET_NAME);
    var wanted = body.headers || Object.keys(body.row || {});
    var row = body.row || {};

    // Header row: written once, in bold, on an empty tab. Columns the website adds later are appended at the end.
    var lastCol = sheet.getLastColumn();
    var headers = lastCol > 0 ? sheet.getRange(1, 1, 1, lastCol).getValues()[0].map(String) : [];
    if (headers.length === 0 || headers.every(function (h) { return h === ''; })) {
      headers = wanted.slice();
      sheet.getRange(1, 1, 1, headers.length).setValues([headers]).setFontWeight('bold');
      sheet.setFrozenRows(1);
    }
    wanted.forEach(function (h) {
      if (headers.indexOf(h) === -1) {
        headers.push(h);
        sheet.getRange(1, headers.length).setValue(h).setFontWeight('bold');
      }
    });

    // Values go to the column with the same NAME, so you may re-order columns or add your own columns on the right.
    var values = headers.map(function (h) { return Object.prototype.hasOwnProperty.call(row, h) ? row[h] : ''; });

    // The same enquiry sent twice (a retry) updates its row instead of adding a second one.
    var target = 0;
    var idCol = headers.indexOf('Enquiry ID') + 1;
    var lastRow = sheet.getLastRow();
    if (idCol > 0 && row['Enquiry ID'] && lastRow > 1) {
      var ids = sheet.getRange(2, idCol, lastRow - 1, 1).getValues();
      for (var i = 0; i < ids.length; i++) {
        if (String(ids[i][0]) === String(row['Enquiry ID'])) { target = i + 2; break; }
      }
    }
    if (!target) { target = Math.max(lastRow, 1) + 1; }

    var range = sheet.getRange(target, 1, 1, values.length);
    range.setNumberFormat('@');   // plain text: phone numbers and anything starting with = + - stay exactly as typed
    range.setValues([values]);

    return reply({ ok: true, row: target });
  } catch (err) {
    return reply({ ok: false, error: String(err) });
  } finally {
    lock.releaseLock();
  }
}

// Opening the web app address in a browser shows this, which is a quick way to check the deployment.
function doGet() {
  return reply({ ok: true, message: 'The enquiry receiver is running. Rows are added by the website (POST).' });
}

function reply(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}
