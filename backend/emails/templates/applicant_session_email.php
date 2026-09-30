<?php
// Applicant session email template — table-based, all CSS inline.
// Expected $data keys: applicant_name, position, date_eat, zoom_link, login_url, support_email
// Returns: ['subject' => string, 'html' => string, 'alt' => string]

function buildApplicantSessionEmail(array $data): array {
    $applicantName = htmlspecialchars($data['applicant_name'] ?? 'Applicant', ENT_QUOTES, 'UTF-8');
    $position      = htmlspecialchars($data['position'] ?? 'the open position', ENT_QUOTES, 'UTF-8');
    $dateEat       = htmlspecialchars($data['date_eat'] ?? '', ENT_QUOTES, 'UTF-8');
    $zoomLink      = htmlspecialchars($data['zoom_link'] ?? '', ENT_QUOTES, 'UTF-8');
    $loginUrl      = htmlspecialchars($data['login_url'] ?? '', ENT_QUOTES, 'UTF-8');
    $supportEmail  = htmlspecialchars($data['support_email'] ?? '', ENT_QUOTES, 'UTF-8');

    $subject = "Interview Scheduled - NCIA";

    $html = <<<HTML
<!DOCTYPE html>
<html><body style="margin:0;padding:0;background-color:#f4f4f4;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">Your interview is scheduled for {$dateEat} — details inside.</div>
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:24px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:10px;overflow:hidden;">
<tr><td align="center" bgcolor="#1E1EA5" style="padding:24px;">
<img src="cid:ncia_logo" alt="NCIA" width="150" style="display:block;border:0;">
<p style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:20px;font-weight:bold;margin:12px 0 0;">Interview Scheduled</p>
</td></tr>
<tr><td style="padding:28px;font-family:Arial,Helvetica,sans-serif;color:#333333;font-size:15px;line-height:1.6;">
<p style="margin:0 0 12px;">Dear {$applicantName},</p>
<p style="margin:0 0 16px;">Your interview for the position of <strong>{$position}</strong> has been scheduled:</p>
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;border-radius:8px;">
<tr><td style="padding:16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#333333;">
<strong>Date &amp; Time (EAT):</strong> {$dateEat}<br>
<strong>Venue:</strong> Online via Zoom
</td></tr>
</table>
<p style="text-align:center;margin:24px 0 8px;">
<table cellpadding="0" cellspacing="0" align="center"><tr>
<td bgcolor="#FFA600" style="border-radius:6px;">
<a href="{$zoomLink}" style="display:inline-block;padding:12px 28px;color:#ffffff;text-decoration:none;font-weight:bold;font-family:Arial,Helvetica,sans-serif;font-size:15px;">Join Zoom Interview</a>
</td></tr></table>
</p>
<p style="font-size:13px;color:#555555;word-break:break-all;">Link: {$zoomLink}</p>
<p style="margin:16px 0 0;">Track your application: <a href="{$loginUrl}" style="color:#1E1EA5;">Log in to the portal</a></p>
<p style="margin:16px 0 0;">Good luck!<br>NCIA Recruitment Team</p>
</td></tr>
<tr><td bgcolor="#1E1EA5" style="padding:16px;text-align:center;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#ffffff;">This is an automated message. Please do not reply directly — contact {$supportEmail}.</td></tr>
</table>
</td></tr></table>
</body></html>
HTML;

    $plainZoom  = $data['zoom_link'] ?? '';
    $plainLogin = $data['login_url'] ?? '';
    $plainName  = $data['applicant_name'] ?? 'Applicant';
    $plainPos   = $data['position'] ?? 'the open position';
    $plainDate  = $data['date_eat'] ?? '';
    $alt = "Dear {$plainName}, your interview for {$plainPos} is on {$plainDate} (EAT). Zoom: {$plainZoom}. Portal: {$plainLogin}. Good luck! - NCIA Recruitment Team";

    return ['subject' => $subject, 'html' => $html, 'alt' => $alt];
}
