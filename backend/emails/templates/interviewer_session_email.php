<?php
// Interviewer session email template — table-based, all CSS inline.
// Expected $data keys: interviewer_name, applicant_name, position, date_eat, dashboard_url, resume_link
// Gateway pattern: no zoom_link / score_url in email — interviewer logs in and opens the session inside the site.
// Returns: ['subject' => string, 'html' => string, 'alt' => string]

function buildInterviewerSessionEmail(array $data): array {
    $interviewerName = htmlspecialchars($data['interviewer_name'] ?? 'Interviewer', ENT_QUOTES, 'UTF-8');
    $applicantName   = htmlspecialchars($data['applicant_name'] ?? 'Applicant', ENT_QUOTES, 'UTF-8');
    $position        = htmlspecialchars($data['position'] ?? 'the open position', ENT_QUOTES, 'UTF-8');
    $dateEat         = htmlspecialchars($data['date_eat'] ?? '', ENT_QUOTES, 'UTF-8');
    $dashboardUrl    = htmlspecialchars($data['dashboard_url'] ?? '', ENT_QUOTES, 'UTF-8');
    $resumeLink      = htmlspecialchars($data['resume_link'] ?? '', ENT_QUOTES, 'UTF-8');

    $subject = "New Interview Assigned - {$applicantName} for {$position}";

    $html = <<<HTML
<!DOCTYPE html>
<html><body style="margin:0;padding:0;background-color:#f4f4f4;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">You are assigned to interview {$applicantName} on {$dateEat}.</div>
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:24px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:10px;overflow:hidden;">
<tr><td align="center" bgcolor="#1E1EA5" style="padding:24px;">
<img src="cid:ncia_logo" alt="NCIA" width="150" style="display:block;border:0;">
<p style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:20px;font-weight:bold;margin:12px 0 0;">New Interview Assigned</p>
</td></tr>
<tr><td style="padding:28px;font-family:Arial,Helvetica,sans-serif;color:#333333;font-size:15px;line-height:1.6;">
<p style="margin:0 0 12px;">Hello {$interviewerName},</p>
<p style="margin:0 0 16px;">You have been assigned to interview:</p>
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;border-radius:8px;">
<tr><td style="padding:16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#333333;">
<strong>Applicant:</strong> {$applicantName} ({$position})<br>
<strong>Date &amp; Time (EAT):</strong> {$dateEat}<br>
<strong>Resume:</strong> <a href="{$resumeLink}" style="color:#1E1EA5;">View resume</a>
</td></tr>
</table>
<p style="margin:0 0 16px;">Log in, then open your assigned session for <strong>{$applicantName}</strong> on <strong>{$dateEat}</strong> — the Zoom link and score card are inside.</p>
<p style="text-align:center;margin:24px 0 8px;">
<table cellpadding="0" cellspacing="0" align="center"><tr>
<td bgcolor="#1E1EA5" style="border-radius:6px;">
<a href="{$dashboardUrl}" style="display:inline-block;padding:12px 28px;color:#ffffff;text-decoration:none;font-weight:bold;font-family:Arial,Helvetica,sans-serif;font-size:15px;">Go to My Sessions</a>
</td></tr></table>
</p>
<p style="font-size:13px;color:#555555;word-break:break-all;">{$dashboardUrl}</p>
</td></tr>
<tr><td bgcolor="#1E1EA5" style="padding:16px;text-align:center;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#ffffff;">NCIA</td></tr>
</table>
</td></tr></table>
</body></html>
HTML;

    $plainInterviewer = $data['interviewer_name'] ?? 'Interviewer';
    $plainApplicant   = $data['applicant_name'] ?? 'Applicant';
    $plainPos         = $data['position'] ?? 'the open position';
    $plainDate        = $data['date_eat'] ?? '';
    $plainDashboard   = $data['dashboard_url'] ?? '';
    $plainResume      = $data['resume_link'] ?? '';
    $alt = "Hello {$plainInterviewer}, you are assigned to interview {$plainApplicant} ({$plainPos}) on {$plainDate} (EAT). Log in and open My Sessions: {$plainDashboard}. Resume: {$plainResume}.";

    return ['subject' => $subject, 'html' => $html, 'alt' => $alt];
}
