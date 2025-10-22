export interface LeadBasicInfo {
  firstName: string
  lastName: string
  email: string
  contactNumber: string
  nationality: string
  state: string
  city: string
  companyName: string
  jobTitle: string
}

export interface LeadAdditionalDetails {
  leadSource: string | null
  leadStatus: string | null
  leadOwner: string | null
  lastContactedDate: string
  scheduledMeetingDate: string
  note: string
  attachments: File[]
}

export interface AddLeadData {
  basicInfo: LeadBasicInfo
  additionalDetails: LeadAdditionalDetails
}
