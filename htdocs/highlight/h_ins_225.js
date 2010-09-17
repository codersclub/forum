window['h_ins']=[
 [
    /^\n\s*;[^\n]*/,
    ['<span style=\'color:008000\'>',3],
    ['</span>',0]
 ],
 [
    /^[^:]\/\/[^\n]*/,
    ['<span style=\'color:008000\'>',3],
    ['</span>',0]
 ],
 [
    /^#[a-z]*/,
    ['<span style=\'color:80C8D7\'>',3],
    ['</span>',0]
 ],
 [
    /^'\w[^']*/,
    ['<span style=\'color:black\'>',3],
    ['</span>',0]
 ],
 [
    /^\[(Setup|Files|Icons|Registry|Run|Dirs|InstallDelete|Messages|UninstallDelete|ini|Types|Components|Tasks|UninstallRun|Code)\]/i,
    ['<span style=\'color:black\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^\{(app|win|sys|src|sd|pf|cf|tmp|fonts|dao|group|sendto|userappdata|commonappdata|userdesktop|commondesktop|userdocs|commondocs|userfavorites|commonfavorites|userprograms|commonprograms|userstartmenu|commonstartmenu|userstartup|commonstartup|usertemplates|commontemplates|computername|groupname|hwnd|wizardhwnd|srcexe|username)\}/i,
    ['<span style=\'color:ff4040\'>',3],
    ['</span>',0]
 ],
 [
    /^(external|var|function|begin|end|procedure|with|do|if|then|else|true|false|for|to|downto|repeat|until|continue|case|of|not|try|finally|except|while)\b/i,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(RunOnceId|ExtraDiskSpaceRequired|WorkingDir|Comment|IconFilename|Iconinde|Name|Description|Types|GroupDescription|Components|Source|DestDir|Destname|CopyMode|Flags|Parameters|Type|Tasks|Filename|Section|MinVersion|OnlyBelowVersion|Attribs|FontInstall|String|Key|Root|ValueType|ValueData|Valuename|Subkey|files|filesandordirs|dirifempty|Check|IconIndex|Permissions):/i,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>:',0]
 ],
 [
    /^(WizardStyle|UninstallStyle|WizardSmallImageFile|ExtraDiskSpaceRequired|CompressLevel|DiskClusterSize|DiskSize|DiskSpanning|DontMergeDuplicateFiles|OutputBaseFilename|OutputDir|ReserveBytes|SourceDir|UseSetupLdr|AdminPrivilegesRequired|AllowNoIcons|AllowRootDirectory|AlwaysCreateUninstallIcon|AlwaysRestart|AlwaysUsePersonalGroup|AppName|AppId|AppMutex|AppPublisher|AppPublisherURL|AppSupportURL|AppUpdatesURL|AppVersion|AppVerName|ChangesAssociations|CreateAppDir|CreateUninstallRegKey|DefaultDirName|DefaultGroupName|DirExistsWarning|DisableAppendDir|DisableDirPage|DisableFinishedPage|DisableProgramGroupPage|DisableStartupPrompt|EnableDirDoesntExistWarning|InfoAfterFile|InfoBeforeFile|LicenseFile|MessagesFile|MinVersion|OnlyBelowVersion|Password|Uninstallable|UninstallDisplayIcon|UninstallDisplayName|UninstallFilesDir|UninstallIconName|UninstallLogMode|UpdateUninstallLogAppName|UsePreviousAppDir|FlatComponentsList|DisableReadyMemo|ShowComponentSizes|UsePreviousGroup|UsePreviousSetupType|AlwaysShowComponentsList|AppCopyright|BackColor|BackColor2|BackColorDirection|BackSolid|WindowShowCaption|WindowStartMaximized|WindowResizable|WindowVisible|WizardImageBackColor|WizardImageFile|Bits|DisableDirExistsWarning|OverwriteUninstRegEntries|DiskSpaceMBLabel|ComponentsDiskSpaceMBLabel|UsePreviousTasks|Compression|SolidCompression|PrivilegesRequired|VersionInfoVersion|ArchitecturesInstallIn64BitMode)=/i,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>=',0]
 ],
 [
    /^(closeonexit|append|disablenouninstallwarning|createonlyiffileexists|dontcloseonexit|runmaximized|runminimized|uninsneveruninstall|useapppaths|createkeyifdoesntexist|uninsdeleteentry|uninsdeletesection|toptobottom|uninsdeletesectionifempty|uninsdeletekeyifempty|createvalueifdoesntexist|deletekey|deletevalue|dontcreatekeynoerror|dontcreatekey|preservestringtype|uninsclearvalue|uninsdeletekey|uninsdeletevalue|comparetimestampalso|confirmoverwrite|deleteafterinstall|fontisnttruetype|isreadme|onlyifdestfileexists|overwritereadonly|regserver|regtypelib|restartreplacesharedfile|skipifsourcedoesntexist|skipifdoesntexist|fixed|deleteafterinstall|uninsalwaysuninstall|uninsneveruninstall|normal|onlyifdoesntexist|alwaysoverwrite|alwaysskipifsameorolder|disablenouninstallwarning|shellexec|postinstall|runmaximized|runminimized|preinstall|noerror|unchecked|skipifsilent|skipifnotsilent|nowait|waituntilidle|readonly|hidden|system|none|expandsz|multisz|dword|binary|exclusive|iscustom|restart|HKCR|HKCU|HKU|HKCC|replacesameversion|dontcopy|touch|HKLM|32|64)/i,
    ['<span style=\'color:802020\'>',3],
    ['</span>',0]
 ],
 [
    /^(InitializeWizard|NextButtonClick|DeInitializeSetup|CurPageChanged|InitializeSetup)/i,
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^[a-z_][0-9a-z_]+/i,
    ['',2]
 ]
];
