<?php
declare(strict_types=1);

function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function tags(array $items): void { echo '<div class="flex flex-wrap gap-2">'; foreach ($items as $item) echo '<span class="tag">'.e($item).'</span>'; echo '</div>'; }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e(ucfirst(str_replace('_',' ',$page)))?> — Portfolio</title><meta name="description" content="Developer portfolio, current systems, projects, activities, resume, and reflections."><link rel="preload" as="image" href="/assets/profile-photo.webp" type="image/webp"><script>try{const saved=localStorage.getItem('portfolio-theme');document.documentElement.dataset.theme=saved||(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light')}catch(error){}</script><style>html{background:#101112}html[data-theme="light"]{background:#f8fafc}</style><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet"><link rel="stylesheet" href="/assets/style.css?v=reflection-print-3"></head><body class="page-<?=e($page)?> bg-white text-black antialiased">
<header class="site-header no-print">
  <div class="site-width header-inner">
    <a href="/" class="brand-logo">
      <span class="brand-dot"></span>
      <span class="brand-name"><?=e($profile['name']??'Cagatin Mark Jed')?></span>
      <span class="brand-tag">BSIT 4th Year</span>
    </a>
    <nav class="top-menu" aria-label="Main Navigation">
      <a href="/" class="<?=$page==='home'?'active':''?>">Home</a>
      <a href="/?page=reflections" class="<?=in_array($page,['reflections','reflection'],true)?'active':''?>">Reflections</a>
      <a href="/?page=resume" class="<?=$page==='resume'?'active':''?>">Resume</a>
      <?php if(owner_logged_in()):?>
      <a href="/?page=admin" class="<?=$page==='admin'?'active':''?>">Admin</a>
      <?php endif;?>
    </nav>
    <div class="header-right">
      <div class="active-viewers" aria-live="polite">
        <span class="active-viewers-dot" aria-hidden="true"></span>
        <span><strong id="active-view-count">—</strong> active now</span>
      </div>
    </div>
  </div>
</header>

<main class="app-main site-width">
<?php if($page==='home'):?>
<?php
$contactLinks=[];
foreach($contact['links']??[] as $link){$contactLinks[strtolower($link['label']??'')]=$link['url']??'';}
$profileSocials=[
    'facebook'=>$contactLinks['facebook']??'https://www.facebook.com/mark.jed.cagatin.2025/',
    'github'=>$contactLinks['github']??'https://github.com/yukino123-bee',
    'linkedin'=>$contactLinks['linkedin']??'https://www.linkedin.com/in/mark-cagatin-9a868a392/',
    'email'=>'mailto:'.($contact['email']??'cagatinmark26@gmail.com'),
];
?>
<!-- Big Center Hero Profile Section -->
<section class="hero-center-section py-12">
  <div class="hero-profile-card">
    <div class="hero-portrait-wrap">
      <picture>
        <source srcset="/assets/profile-photo.webp" type="image/webp">
        <img src="/assets/profile-photo.png" alt="Portrait of Cagatin Mark Jed" class="hero-portrait-img" width="340" height="340" fetchpriority="high" decoding="async">
      </picture>
    </div>
    <div class="hero-info-content">
      <div class="hero-role-badge"><?=e($profile['role']??'Full-Stack Developer')?></div>
      <h1 class="hero-name mt-3">
        <button id="profile-name-button" class="profile-name-button text-4xl font-bold" type="button" aria-expanded="false" aria-controls="profile-full-info">
          <?=e($profile['name']??'Cagatin Mark Jed')?>
        </button>
      </h1>

      <div id="profile-full-info" class="profile-full-info" hidden>
        <p class="label">Full Information</p>
        <dl>
          <div><dt>Name</dt><dd><?=e($profile['full_name']??'Mark Jed M. Cagatin')?></dd></div>
          <div><dt>Age</dt><dd><?=e($profile['age']??'21')?></dd></div>
          <div><dt>Birthdate</dt><dd><?=e($profile['birthdate']??'March 11, 2005')?></dd></div>
          <div><dt>School</dt><dd><?=e($profile['school']??'JH Cerilles State College')?></dd></div>
          <div><dt>Course &amp; Year</dt><dd><?=e($profile['course_year']??'Bachelor of Information Technology — 4th Year')?></dd></div>
          <div><dt>Dream Job</dt><dd><?=e($profile['dream_job']??'Developer')?></dd></div>
        </dl>
      </div>

      <p class="hero-intro-text text-lg mt-4"><?=e($profile['intro']??'')?></p>
      <p class="hero-bio-text text-sm mt-3"><?=e($profile['biography']??'')?></p>

      <div class="hero-meta-row mt-4">
        <span class="meta-item">📍 <?=e($profile['location']??'Metro Manila, Philippines')?></span>
        <span class="meta-item">🟢 <?=e($profile['availability']??'Open to collaborations')?></span>
      </div>

<?php
$socialIcons = [
  'facebook' => '<svg class="social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
  'github' => '<svg class="social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>',
  'linkedin' => '<svg class="social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>',
  'email' => '<svg class="social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M0 3v18h24v-18h-24zm21.518 2l-9.518 7.713-9.518-7.713h19.036zm-19.518 14v-11.817l10 8.104 10-8.104v11.817h-20z"/></svg>'
];
?>
      <div class="hero-social-links mt-6">
        <?php foreach($profileSocials as $label=>$url):?>
          <a href="<?=e($url)?>" target="_blank" rel="noreferrer" class="social-link-btn" aria-label="<?=e($label)?>">
            <?=$socialIcons[strtolower($label)]??''?>
            <span><?=e(ucfirst($label))?></span><span aria-hidden="true">↗</span>
          </a>
        <?php endforeach;?>
      </div>
    </div>
  </div>
</section>

<!-- Currently Building Section -->
<section class="currently-building-section py-8">
  <div class="section-heading mb-6">
    <span>01</span>
    <h2 class="text-2xl font-bold">Currently Building</h2>
  </div>
  <div class="building-cards-grid">
    <!-- Empty Building Card 1 -->
    <div class="building-card empty-plus-card">
      <div class="plus-icon-wrap">
        <span class="plus-icon">+</span>
      </div>
      <span class="coming-soon-label mt-3">Coming Soon</span>
    </div>

    <!-- Empty Building Card 2 -->
    <div class="building-card empty-plus-card">
      <div class="plus-icon-wrap">
        <span class="plus-icon">+</span>
      </div>
      <span class="coming-soon-label mt-3">Coming Soon</span>
    </div>

    <!-- Empty Building Card 3 -->
    <div class="building-card empty-plus-card">
      <div class="plus-icon-wrap">
        <span class="plus-icon">+</span>
      </div>
      <span class="coming-soon-label mt-3">Coming Soon</span>
    </div>
  </div>
</section>

<!-- Technologies & Skills Section -->
<section class="skills-section pt-12 pb-6 mt-6">
  <div class="clean-card skills-card">
    <div class="skills-header-row">
      <div>
        <span class="card-label">Technical Stack &amp; Expertise</span>
        <h3 class="text-xl font-bold mt-1">Skills &amp; Environment</h3>
      </div>
      <div class="environment-badge">
        <span class="env-dot"></span>
        <span>Fedora Linux Workstation</span>
      </div>
    </div>

    <div class="skills-tags-wrap mt-4">
      <?php tags($profile['skills']??[]);?>
    </div>
  </div>
</section>

<section class="certificates-section py-10" aria-labelledby="certificates-title">
  <div class="section-heading mb-6">
    <span>02</span>
    <h2 class="text-2xl font-bold" id="certificates-title">Certificates &amp; Awards</h2>
  </div>
  <div class="certificates-grid">
    <a class="certificate-card" href="/uploads/certificates/cyberian-fest-hackathon-2026.png" target="_blank" rel="noreferrer">
      <div class="certificate-preview certificate-preview-rotated">
        <picture>
          <source srcset="/uploads/certificates/cyberian-fest-hackathon-2026.webp" type="image/webp">
          <img src="/uploads/certificates/cyberian-fest-hackathon-2026.png" alt="Certificate of Recognition for first place in the Cyberian Fest 2026 Hackathon" loading="lazy" decoding="async" width="1400" height="2047">
        </picture>
      </div>
      <div class="certificate-details">
        <p class="certificate-kind">Certificate of Recognition</p>
        <h2>1st Place — Cyberian Fest Hackathon</h2>
        <p>JH Cerilles State College · May 8, 2026</p>
        <span>View certificate ↗</span>
      </div>
    </a>
    <a class="certificate-card" href="/uploads/certificates/flowcharting-cpp-programming-2026.pdf" target="_blank" rel="noreferrer">
      <div class="certificate-preview">
        <picture>
          <source srcset="/uploads/certificates/flowcharting-cpp-programming-2026.webp" type="image/webp">
          <img src="/uploads/certificates/flowcharting-cpp-programming-2026.png" alt="Certificate of Attendance for Introduction to Flowcharting and C++ Programming" loading="lazy" decoding="async" width="1404" height="993">
        </picture>
      </div>
      <div class="certificate-details">
        <p class="certificate-kind">Certificate of Attendance</p>
        <h2>Introduction to Flowcharting &amp; C++ Programming</h2>
        <p>Project Tuklas Teknolohiya · August 16, 2026</p>
        <span>View certificate ↗</span>
      </div>
    </a>
  </div>
</section>
<?php elseif($page==='about'):?>
<?php page_head('About','Curious by nature. Deliberate in practice.',$profile['biography']??'');?><section class="site-width grid gap-10 py-16 md:grid-cols-[1fr_320px]"><div><h2 class="text-3xl font-semibold">Current system</h2><h3 class="mt-6 text-2xl"><?=e($system['name']??'')?></h3><p class="mt-3 text-neutral-600"><?=e($system['description']??'')?></p><p class="mt-6"><strong>Current focus:</strong> <?=e($system['current_focus']??'')?></p><p><strong>Next milestone:</strong> <?=e($system['next_milestone']??'')?></p></div><aside class="panel"><p class="label">Skills</p><div class="mt-5"><?php tags($profile['skills']??[]);?></div></aside></section>
<?php elseif($page==='activities'):?>
<?php page_head('Activities','Activities that sharpen the work.','A clear record of the work, the process, and the lessons that followed.');?><section class="site-width grid gap-5 py-16 md:grid-cols-2"><?php foreach($items as $item):$d=$item['data'];?><a class="panel" href="/?page=activity&slug=<?=e($item['slug'])?>"><p class="label"><?=e($d['date']??$d['category']??'')?></p><h2 class="mt-4 text-2xl font-semibold"><?=e($d['title']??$item['title'])?></h2><p class="mt-3 text-neutral-600"><?=e($d['summary']??'')?></p><span class="mt-6 inline-block font-semibold">Read →</span></a><?php endforeach;?></section>
<?php elseif($page==='activity'): if(!$item):http_response_code(404);echo '<div class="site-width py-20"><h1>Not found</h1></div>';else:$d=$item['data'];?>
<?php page_head(ucfirst($page),$d['title']??$item['title'],$d['summary']??'');?><article class="site-width max-w-3xl py-16"><p class="text-lg text-neutral-700"><?=nl2br(e($d['description']??''))?></p><?php $list=$d['highlights']??$d['outcomes']??[];if($list):?><h2 class="mt-10 text-2xl font-semibold">Highlights</h2><ul class="mt-4 list-disc space-y-2 pl-6"><?php foreach($list as $line):?><li><?=e($line)?></li><?php endforeach;?></ul><?php endif;?></article><?php endif;?>
<?php elseif($page==='resume'): $d=$item['data']??[];?>
<?php $resumeContent=array_diff_key($d,array_flip(['resume_version','uploaded_pdf','uploaded_pdf_name','uploaded_word','uploaded_word_name']));$resumeIsBlank=!array_filter($resumeContent,static fn($value):bool=>$value!==''&&$value!==[]&&$value!==null);if($resumeIsBlank):?><style>.resume-source>*{display:none}</style><?php endif;?>
<?php if(!empty($d['uploaded_pdf'])):?><div class="site-width resume-uploaded-preview"><div class="imported-pdf-shell"><iframe src="<?=e($d['uploaded_pdf'])?>" title="Imported resume PDF"></iframe><a class="button-outline no-print" href="<?=e($d['uploaded_pdf'])?>" download="<?=e($d['uploaded_pdf_name']??'resume.pdf')?>">Download PDF</a></div></div><?php endif;?>
<section class="site-width py-10 resume-page"><div class="no-print mb-5"><p class="label">Resume document</p><h1 class="mt-2 text-3xl font-semibold">Resume</h1></div><div class="paper-shell"><article class="paper resume-source"><header class="resume-source-header"><h1><?=e($d['name']??'')?></h1><p class="resume-source-headline"><?=e($d['headline']??'')?></p><p><?=e($d['location']??'')?> <span>|</span> <?=e($d['phone']??'')?> <span>|</span> <?=e($d['email']??'')?> <span>|</span> <?=e($d['github']??'')?> <span>|</span><br><?=e($d['linkedin']??'')?> <span>|</span> <?=e($d['website']??'')?></p></header><section class="resume-source-section"><h2>Professional Summary</h2><p><?=e($d['summary']??'')?></p></section><section class="resume-source-section"><h2>Technical Skills</h2><dl class="resume-skills"><?php foreach($d['skills']??[] as $skill):?><dt><?=e($skill['group']??'')?></dt><dd><?=e(implode(', ',$skill['items']??[]))?></dd><?php endforeach;?></dl></section><section class="resume-source-section"><h2>Selected Projects</h2><ul class="resume-projects"><?php foreach($d['projects']??[] as $project):?><li><p><strong><?=e($project['name']??'')?></strong> <span>|</span> <em><?=e($project['technologies']??'')?></em></p><p><?=e($project['description']??'')?></p></li><?php endforeach;?></ul></section><section class="resume-source-section"><h2>Education</h2><p><strong><?=e($d['education_degree']??'')?></strong> <span>|</span> <?=e($d['education_period']??'')?></p><p><?=e($d['education_school']??'')?></p></section><section class="resume-source-section"><h2>Experience / Activities</h2><p><strong><?=e($d['activity_role']??'')?></strong> <span>|</span> <?=e($d['activity_organization']??'')?> <span>|</span> <?=e($d['activity_year']??'')?></p><p class="resume-activity-detail"><?=e($d['activity_details']??'')?></p></section><section class="resume-source-section"><h2>Certifications &amp; Achievements</h2><p><?=e($d['certifications']??'')?></p></section></article></div><?php if(!empty($d['uploaded_word'])):?><p class="no-print mt-5"><a class="button-outline" href="<?=e($d['uploaded_word'])?>" download="<?=e($d['uploaded_word_name']??'resume.docx')?>">Download uploaded Word file</a></p><?php endif;?></section>
<?php elseif($page==='reflection'): if(!$item):http_response_code(404);echo 'Not found';else:$d=$item['data'];$meta=array_filter([$d['course']??'',$d['instructor']??'',$d['date']??'']);?>
<section class="site-width py-10"><div class="no-print mb-5 flex items-center justify-between"><div><p class="label">A4 document</p><h1 class="mt-2 text-3xl font-semibold">Reflection</h1></div><?php if(!empty($d['uploaded_pdf'])):?><a class="button" href="<?=e($d['uploaded_pdf'])?>" target="_blank" rel="noreferrer">Open / Print PDF</a><?php else:?><button onclick="window.print()" class="button">Export as PDF</button><?php endif;?></div><?php if(!empty($d['uploaded_pdf'])):?><div class="imported-pdf-shell"><iframe src="<?=e($d['uploaded_pdf'])?>" title="Imported reflection PDF"></iframe><a class="button-outline no-print" href="<?=e($d['uploaded_pdf'])?>" download="<?=e($d['uploaded_pdf_name']??'reflection.pdf')?>">Download PDF</a></div><?php else:?><div class="paper-shell"><article class="paper reflection <?=e($layout['reflection_template'])?>" style="--doc-size:<?=e($layout['font_size'])?>pt;--doc-line:<?=e($layout['line_height'])?>;--doc-space:<?=e($layout['section_spacing'])?>px"><header class="reflection-document-header"><h1><?=e($d['title']??'Reflection Paper')?></h1><?php if($meta):?><p class="reflection-meta"><?=e(implode(' · ',$meta))?></p><?php endif;?></header><?php if(!empty($d['body'])):?><div class="reflection-body"><?php foreach(preg_split('/\R{2,}/',trim($d['body'])) as $paragraph):?><p><?=e($paragraph)?></p><?php endforeach;?></div><?php else:?><?php if(!empty($d['activity']))doc_section('Activity','<p>'.e($d['activity']).'</p>');foreach(['Experience'=>'experience','Observations'=>'observations','Learning'=>'learning','Next Steps'=>'next_steps','Conclusion'=>'conclusion'] as $label=>$key)doc_section($label,'<p>'.e($d[$key]??'').'</p>');?><?php endif;?></article></div><article class="print-document" aria-hidden="true"><header><h1><?=e($d['title']??'Reflection Paper')?></h1><?php if($meta):?><p><?=e(implode(' · ',$meta))?></p><?php endif;?></header><?php if(!empty($d['body'])):?><?php foreach(preg_split('/\R{2,}/',trim($d['body'])) as $paragraph):?><p><?=e($paragraph)?></p><?php endforeach;?><?php else:?><?php if(!empty($d['activity'])):?><section><h2>Activity</h2><p><?=e($d['activity'])?></p></section><?php endif;?><?php foreach(['Experience'=>'experience','Observations'=>'observations','Learning'=>'learning','Next Steps'=>'next_steps','Conclusion'=>'conclusion'] as $label=>$key):?><section><h2><?=e($label)?></h2><p><?=e($d[$key]??'')?></p></section><?php endforeach;?><?php endif;?></article><?php endif;?><?php if(!empty($d['uploaded_word'])):?><p class="no-print mt-5"><a class="button-outline" href="<?=e($d['uploaded_word'])?>" download="<?=e($d['uploaded_word_name']??'reflection.docx')?>">Download uploaded Word file</a></p><?php endif;?></section><?php endif;?>
<?php elseif($page==='login'):?><section class="site-width flex min-h-[70vh] items-center justify-center py-16"><form method="post" class="panel w-full max-w-md"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><input type="hidden" name="action" value="login"><p class="label">Owner access</p><h1 class="mt-4 text-3xl font-semibold">Sign in</h1><?php if($error):?><p class="mt-4 border border-neutral-400 bg-neutral-100 p-3"><?=e($error)?></p><?php endif;?><label class="mt-6 block text-sm font-semibold">Email<input class="field" name="email" type="email" required></label><label class="mt-4 block text-sm font-semibold">Password<input class="field" name="password" type="password" required></label><button class="button mt-6 w-full">Sign in</button></form></section>
<?php elseif($page==='admin'):require_owner();?><?php page_head('Admin','Portfolio content','Edit drafts and publish only when the content is ready.');?><section class="site-width admin-dashboard py-12"><?php if($error):?><p class="admin-alert"><?=e($error)?></p><?php endif;?><div class="admin-toolbar"><div class="admin-toolbar-actions"><a class="button-outline" href="/?page=layouts">Paper templates</a><?php foreach(['activity','reflection'] as $newType):?><form method="post"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><input type="hidden" name="action" value="create"><input type="hidden" name="type" value="<?=$newType?>"><button class="button-outline">New <?=e($newType)?></button></form><?php endforeach;?><?php if(!array_filter($items,fn($entry)=>$entry['type']==='reflection')):?><form method="post"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><input type="hidden" name="action" value="restore_reflection"><button class="button">Restore reflection</button></form><?php endif;?></div><form method="post"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><input type="hidden" name="action" value="logout"><button class="admin-signout">Sign out</button></form></div><div class="admin-summary"><div><strong><?=count($items)?></strong><span>Total entries</span></div><div><strong><?=count(array_filter($items,fn($entry)=>!empty($entry['is_published'])))?></strong><span>Published</span></div><div><strong><?=count(array_filter($items,fn($entry)=>empty($entry['is_published'])))?></strong><span>Drafts</span></div></div><div class="admin-content-grid"><?php foreach($items as $item):?><article class="admin-content-card"><div class="admin-card-top"><span class="admin-type"><?=e(str_replace('_',' ',$item['type']))?></span><span class="admin-status <?=$item['is_published']?'is-published':'is-draft'?>"><?=$item['is_published']?'Published':'Draft'?></span></div><h2><?=e($item['title'])?></h2><?php if(in_array($item['type'],['resume','reflection'],true)):?><form class="pdf-import-form" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><input type="hidden" name="action" value="upload_pdf"><input type="hidden" name="id" value="<?=e($item['id'])?>"><label><span>Import PDF</span><input name="pdf" type="file" accept="application/pdf,.pdf" required></label><button>Upload</button></form><?php endif;?><div class="admin-card-footer"><span>Content entry</span><a href="/?page=edit&id=<?=e($item['id'])?>">Edit <span aria-hidden="true">→</span></a></div></article><?php endforeach;?></div></section>
<?php elseif($page==='edit'):require_owner();if(!$item):echo 'Not found';else:$d=$item['data'];?>
<section class="site-width document-edit-page py-12"><a href="/?page=admin">← Admin</a><h1 class="mt-5 text-3xl font-semibold">Edit <?=e($item['title'])?></h1><?php if($error):?><p class="mt-4 bg-neutral-100 p-3"><?=e($error)?></p><?php endif;?><form method="post" class="mt-8 space-y-5"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><input type="hidden" name="id" value="<?=e($item['id'])?>"><?php if($item['type']==='profile'):?><input type="hidden" name="profile_form" value="1"><input type="hidden" name="title" value="<?=e($item['title'])?>"><input type="hidden" name="slug" value="<?=e($item['slug'])?>"><input type="hidden" name="sort_order" value="<?=e($item['sort_order'])?>"><div class="profile-editor-heading"><img src="/assets/profile-photo.png" alt="Current profile photo"><div><p class="label">Current profile</p><h2 class="mt-2 text-2xl font-semibold"><?=e($d['name']??'Cagatin Mark Jed')?></h2></div></div><div class="grid gap-5 md:grid-cols-2"><?php editor_input('name','Display name',$d['name']??'');editor_input('role','Role',$d['role']??'');editor_input('location','Location',$d['location']??'');editor_input('availability','Availability',$d['availability']??'');?></div><div class="profile-editor-section"><p class="label">Expandable full information</p><div class="grid gap-5 md:grid-cols-2"><?php editor_input('full_name','Full name',$d['full_name']??'Mark Jed M. Cagatin');editor_input('age','Age',$d['age']??'21','number');editor_input('birthdate','Birthdate',$d['birthdate']??'March 11, 2005');editor_input('school','School',$d['school']??'JH Cerilles State College');editor_input('course_year','Course & Year',$d['course_year']??'Bachelor of Information Technology — 4th Year');editor_input('dream_job','Dream Job',$d['dream_job']??'Developer');?></div></div><?php editor_textarea('intro','Introduction',$d['intro']??'');editor_textarea('biography','Biography',$d['biography']??'');editor_input('skills','Skills',implode(', ',$d['skills']??[]));?>
<?php elseif($item['type']==='resume'):?><input type="hidden" name="resume_form" value="1"><input type="hidden" name="title" value="<?=e($item['title'])?>"><input type="hidden" name="slug" value="<?=e($item['slug'])?>"><input type="hidden" name="sort_order" value="<?=e($item['sort_order'])?>"><div class="document-editor-shell"><div class="document-editor-paper resume-editor"><header class="document-editor-header"><?php editor_input('name','Full name',$d['name']??'');editor_input('headline','Headline',$d['headline']??'');?><div class="document-editor-contact"><?php editor_input('location','Location',$d['location']??'');editor_input('phone','Phone',$d['phone']??'');editor_input('email','Email',$d['email']??'','email');editor_input('github','GitHub',$d['github']??'');editor_input('linkedin','LinkedIn',$d['linkedin']??'');editor_input('website','Portfolio',$d['website']??'');?></div></header><section class="document-editor-section"><h2>Professional Summary</h2><?php editor_textarea('summary','Summary',$d['summary']??'');?></section><section class="document-editor-section"><h2>Technical Skills</h2><?php foreach($d['skills']??[] as $skill):?><div class="document-editor-skill"><?php editor_input('skill_group[]','Group',$skill['group']??'');editor_input('skill_items[]','Skills — separated by commas',implode(', ',$skill['items']??[]));?></div><?php endforeach;?></section><section class="document-editor-section"><h2>Selected Projects</h2><?php foreach($d['projects']??[] as $project):?><div class="document-editor-entry"><div class="document-editor-row"><?php editor_input('project_name[]','Project name',$project['name']??'');editor_input('project_technologies[]','Technology stack',$project['technologies']??'');?></div><?php editor_textarea('project_description[]','Description',$project['description']??'');?></div><?php endforeach;?></section><section class="document-editor-section"><h2>Education</h2><div class="document-editor-row"><?php editor_input('education_degree','Degree',$d['education_degree']??'');editor_input('education_period','Period',$d['education_period']??'');?></div><?php editor_input('education_school','School',$d['education_school']??'');?></section><section class="document-editor-section"><h2>Experience / Activities</h2><div class="document-editor-row"><?php editor_input('activity_role','Role / activity',$d['activity_role']??'');editor_input('activity_organization','Organization',$d['activity_organization']??'');editor_input('activity_year','Year',$d['activity_year']??'');?></div><?php editor_textarea('activity_details','Details',$d['activity_details']??'');?></section><section class="document-editor-section"><h2>Certifications &amp; Achievements</h2><?php editor_textarea('certifications','Certifications',$d['certifications']??'');?></section></div></div>
<?php elseif($item['type']==='reflection'):?><input type="hidden" name="reflection_form" value="1"><input type="hidden" name="slug" value="<?=e($item['slug'])?>"><input type="hidden" name="sort_order" value="<?=e($item['sort_order'])?>"><div class="document-editor-shell"><div class="document-editor-paper reflection-editor"><header class="document-editor-header"><?php editor_input('title','Reflection title',$d['title']??$item['title']);?><div class="document-editor-contact"><?php editor_input('course','Course',$d['course']??'');editor_input('instructor','Instructor',$d['instructor']??'');editor_input('date','Date',$d['date']??'','date');?></div></header><section class="document-editor-section"><h2>Reflection</h2><?php editor_textarea('body','Separate paragraphs with a blank line',$d['body']??'');?></section></div></div>
<?php else:?><label class="block font-semibold">Title<input class="field" name="title" value="<?=e($item['title'])?>" required></label><label class="block font-semibold">URL slug<input class="field" name="slug" value="<?=e($item['slug'])?>" pattern="[a-z0-9-]+" required></label><label class="block font-semibold">Display order<input class="field" name="sort_order" type="number" value="<?=e($item['sort_order'])?>"></label><label class="block font-semibold">Content data<textarea class="field min-h-96 font-mono text-sm" name="data" required><?=e(json_encode($d,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE))?></textarea></label><?php endif;?><div class="editor-actions"><button class="button-outline" name="action" value="save">Save draft</button><button class="button" name="action" value="publish">Save & publish</button><?php if($item['is_published']):?><button class="button-outline" name="action" value="unpublish">Unpublish</button><?php endif;?><?php if(in_array($item['type'],['project','activity'],true)):?><button class="button-outline" onclick="return confirm('Delete this item?')" name="action" value="delete">Delete</button><?php endif;?></div></form></section><?php endif;?>
<?php elseif($page==='layouts'):require_owner()?><section class="site-width max-w-3xl py-12"><p class="label">Paper format</p><h1 class="mt-4 text-4xl font-semibold">Document format</h1><p class="mt-3 text-neutral-600">All generated resumes and reflections use a plain A4 layout: Times New Roman, 12 pt type, 1.5 line spacing, and one-inch margins.</p></section>
<?php else:http_response_code(404);?><section class="site-width py-24"><h1 class="text-5xl font-semibold">Page not found</h1></section><?php endif;?>
</main>
<nav class="mobile-bottom-nav no-print" aria-label="Mobile Navigation">
  <div class="mobile-bottom-nav-inner">
    <a href="/" class="mobile-nav-item <?=$page==='home'?'active':''?>" aria-label="Home">
      <div class="mobile-nav-icon-wrap">
        <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
      </div>
      <span class="mobile-nav-label">Home</span>
    </a>
    <a href="/?page=reflections" class="mobile-nav-item <?=in_array($page,['reflections','reflection'],true)?'active':''?>" aria-label="Reflections">
      <div class="mobile-nav-icon-wrap">
        <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
      </div>
      <span class="mobile-nav-label">Reflections</span>
    </a>
    <a href="/?page=resume" class="mobile-nav-item <?=$page==='resume'?'active':''?>" aria-label="Resume">
      <div class="mobile-nav-icon-wrap">
        <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
      </div>
      <span class="mobile-nav-label">Resume</span>
    </a>
    <?php if(owner_logged_in()):?>
    <a href="/?page=admin" class="mobile-nav-item <?=$page==='admin'?'active':''?>" aria-label="Admin">
      <div class="mobile-nav-icon-wrap">
        <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
      </div>
      <span class="mobile-nav-label">Admin</span>
    </a>
    <?php endif;?>
  </div>
</nav>
<footer class="app-footer no-print border-t border-neutral-200"><div class="site-width flex flex-wrap items-center justify-center gap-3 py-7 text-sm text-neutral-500 text-center"><span>© <?=date('Y')?> <?=e($profile['name']??'Portfolio')?></span><span aria-hidden="true" class="opacity-40">·</span><a href="/?page=login" class="hover:text-emerald-500 transition-colors">Owner</a></div></footer><script src="/assets/app.js?v=reflection-print-3"></script></body></html>
<?php
function page_head(string $label,string $title,string $description):void{echo '<header class="border-b border-neutral-200"><div class="site-width py-16"><p class="label">'.e($label).'</p><h1 class="mt-4 text-5xl font-semibold tracking-[-.04em]">'.e($title).'</h1><p class="mt-5 max-w-3xl text-lg text-neutral-600">'.e($description).'</p></div></header>';}
function doc_section(string $title,string $html):void{echo '<section class="doc-section"><h2>'.e($title).'</h2>'.$html.'</section>';}
function doc_entries(string $title,array $entries,string $primary,string $secondary):void{ob_start();foreach($entries as $entry){echo '<article class="mb-3 break-inside-avoid"><div class="flex justify-between gap-4"><strong>'.e($entry[$primary]??'').' · '.e($entry[$secondary]??'').'</strong><span>'.e($entry['period']??'').'</span></div>';if(isset($entry['details'])&&is_array($entry['details'])){echo '<ul class="list-disc pl-5">';foreach($entry['details'] as $x)echo '<li>'.e($x).'</li>';echo '</ul>';}elseif(isset($entry['details']))echo '<p>'.e($entry['details']).'</p>';echo '</article>';}doc_section($title,ob_get_clean());}
function select_field(string $name,string $label,array $choices,array $data):void{echo '<label class="font-semibold">'.e($label).'<select class="field" name="'.e($name).'">';foreach($choices as $value=>$text)echo '<option value="'.e($value).'" '.(($data[$name]??'')===$value?'selected':'').'>'.e($text).'</option>';echo '</select></label>';}
function number_field(string $name,string $label,float $min,float $max,float $step,array $data):void{echo '<label class="font-semibold">'.e($label).'<input class="field" type="number" name="'.e($name).'" min="'.$min.'" max="'.$max.'" step="'.$step.'" value="'.e($data[$name]??'').'"></label>';}
function editor_input(string $name,string $label,mixed $value,string $type='text'):void{echo '<label class="document-editor-field"><span>'.e($label).'</span><input name="'.e($name).'" type="'.e($type).'" value="'.e($value).'" required></label>';}
function editor_textarea(string $name,string $label,mixed $value):void{echo '<label class="document-editor-field"><span>'.e($label).'</span><textarea name="'.e($name).'" rows="3" required>'.e($value).'</textarea></label>';}
