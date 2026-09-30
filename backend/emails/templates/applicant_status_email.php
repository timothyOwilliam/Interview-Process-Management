<?php
// Applicant status-update email template — table-based, all CSS inline.
// Gateway pattern: no secrets in email, applicant logs in to see details.
// Expected $data keys: applicant_name, position, new_status (pending|shortlisted|rejected), login_url, support_email
// Returns: ['subject' => string, 'html' => string, 'alt' => string]

function buildApplicantStatusEmail(array $data): array {
    $applicantName = htmlspecialchars($data['applicant_name'] ?? 'Applicant', ENT_QUOTES, 'UTF-8');
    $position      = htmlspecialchars($data['position'] ?? 'the open position', ENT_QUOTES, 'UTF-8');
    $status        = strtolower(trim($data['new_status'] ?? 'pending'));
    $loginUrl      = htmlspecialchars($data['login_url'] ?? '', ENT_QUOTES, 'UTF-8');
    $supportEmail  = htmlspecialchars($data['support_email'] ?? '', ENT_QUOTES, 'UTF-8');

    if (!in_array($status, ['pending', 'shortlisted', 'rejected'], true)) {
        $status = 'pending';
    }
    $statusLabel = ucfirst($status);

    // Per-status copy + badge color. Green/red/grey match style.css message conventions.
    switch ($status) {
        case 'shortlisted':
            $heading    = 'You Have Been Shortlisted';
            $badgeBg    = '#28a745';
            $message    = "Congratulations! Your application for the position of <strong>{$position}</strong> has been <strong>shortlisted</strong>. Our team will contact you shortly with the next steps.";
            $cta        = true;
            $preheader  = "Good news — you have been shortlisted for {$position}.";
            $subject    = "You Have Been Shortlisted - {$position}";
            $plainExtra = 'Our team will contact you shortly with the next steps.';
            break;
        case 'rejected':
            $heading    = 'Update on Your Application';
            $badgeBg    = '#dc3545';
            $message    = "Thank you for your interest in the position of <strong>{$position}</strong>. After careful consideration, we will not be moving forward with your application at this time. We appreciate the effort you put in and wish you success in your job search.";
            $cta        = false;
            $preheader  = "An update on your application for {$position}.";
            $subject    = "Update on Your Application - {$position}";
            $plainExtra = 'We will not be moving forward with your application at this time. Thank you for your interest.';
            break;
        default: // pending
            $heading    = 'Your Application Is Under Review';
            $badgeBg    = '#6c757d';
            $message    = "Your application for the position of <strong>{$position}</strong> is currently <strong>under review</strong>. We will notify you once there is an update.";
            $cta        = true;
            $preheader  = "Your application for {$position} is under review.";
            $subject    = "Your Application Is Under Review - {$position}";
            $plainExtra = 'We will notify you once there is an update.';
            break;
    }

    $ctaBlock = '';
    if ($cta) {
        $ctaBlock = <<<HTML
<p style="text-align:center;margin:24px 0 8px;">
<table cellpadding="0" cellspacing="0" align="center"><tr>
<td bgcolor="#1E1EA5" style="border-radius:6px;">
<a href="{$loginUrl}" style="display:inline-block;padding:12px 28px;color:#ffffff;text-decoration:none;font-weight:bold;font-family:Arial,Helvetica,sans-serif;font-size:15px;">Log In to Check Status</a>
</td></tr></table>
</p>
<p style="font-size:13px;color:#555555;word-break:break-all;">{$loginUrl}</p>
HTML;
    }

    $html = <<<HTML
<!DOCTYPE html>
<html><body style="margin:0;padding:0;background-color:#f4f4f4;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{$preheader}</div>
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:24px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:10px;overflow:hidden;">
<tr><td align="center" bgcolor="#1E1EA5" style="padding:24px;">
<img src="cid:ncia_logo" alt="NCIA" width="150" style="display:block;border:0;">
<p style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:20px;font-weight:bold;margin:12px 0 0;">{$heading}</p>
</td></tr>
<tr><td style="padding:28px;font-family:Arial,Helvetica,sans-serif;color:#333333;font-size:15px;line-height:1.6;">
<p style="margin:0 0 12px;">Dear {$applicantName},</p>
<p style="margin:0 0 16px;">
<span style="display:inline-block;background-color:{$badgeBg};color:#ffffff;font-size:13px;font-weight:bold;padding:4px 14px;border-radius:20px;">{$statusLabel}</span>
</p>
<p style="margin:0 0 16px;">{$message}</p>
{$ctaBlock}
<p style="margin:16px 0 0;">NCIA Recruitment Team</p>
</td></tr>
<tr><td bgcolor="#1E1EA5" style="padding:16px;text-align:center;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#ffffff;">This is an automated message. Please do not reply directly — contact {$supportEmail}.</td></tr>
</table>
</td></tr></table>
</body></html>
HTML;

    $plainName  = $data['applicant_name'] ?? 'Applicant';
    $plainPos   = $data['position'] ?? 'the open position';
    $plainLogin = $data['login_url'] ?? '';
    $alt = "Dear {$plainName}, update on your application for {$plainPos}: {$statusLabel}. {$plainExtra} Portal: {$plainLogin}. - NCIA Recruitment Team";

    return ['subject' => $subject, 'html' => $html, 'alt' => $alt];
}
