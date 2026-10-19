/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 *
 * Zero Delivery Date's product page estimate (templates/product/eta.phtml):
 * the earliest delivery day by the store's rules (lead days, one day more
 * past the cut-off hour, days without delivery), in the store's time zone,
 * and the time left before the cut-off. Re-counted every minute.
 */
(function () {
    'use strict';

    var line = document.querySelector('[data-zero-eta]');
    var settingsEl = document.getElementById('zero-eta-settings');
    if (!line || !settingsEl || !window.Intl) {
        return;
    }
    var s = JSON.parse(settingsEl.textContent);
    var text = line.querySelector('[data-zero-eta-text]');

    // The store's wall clock now: year, month, day, hour, minute.
    function storeNow() {
        var parts = {};
        new Intl.DateTimeFormat('en-US', {
            timeZone: s.timeZone, hourCycle: 'h23',
            year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric'
        }).formatToParts(new Date()).forEach(function (p) { parts[p.type] = Number(p.value); });
        return parts;
    }

    function iso(date) {
        return date.toISOString().slice(0, 10);
    }

    function render() {
        var now = storeNow();
        var pastCutoff = s.cutoff !== null && now.hour >= s.cutoff;
        var day = new Date(Date.UTC(now.year, now.month - 1, now.day + s.lead + (pastCutoff ? 1 : 0), 12));
        var found = null;
        for (var i = 0; i < Math.max(1, s.days); i++) {
            if (s.noWeekdays.indexOf(day.getUTCDay()) === -1 && s.noDates.indexOf(iso(day)) === -1) {
                found = day;
                break;
            }
            day = new Date(day.getTime() + 86400000);
        }
        if (!found) {
            line.hidden = true;
            return;
        }
        var date = new Intl.DateTimeFormat(s.locale, { weekday: 'long', month: 'long', day: 'numeric', timeZone: 'UTC' }).format(found);
        if (s.cutoff !== null && !pastCutoff) {
            var left = s.cutoff * 60 - (now.hour * 60 + now.minute);
            var time = left >= 60
                ? s.text.hours.replace('{h}', Math.floor(left / 60)).replace('{m}', left % 60)
                : s.text.minutes.replace('{m}', left);
            text.textContent = s.text.cutoff.replace('{time}', time).replace('{date}', date);
        } else {
            text.textContent = s.text.plain.replace('{date}', date);
        }
        line.hidden = false;
    }

    render();
    setInterval(render, 60000);
})();
